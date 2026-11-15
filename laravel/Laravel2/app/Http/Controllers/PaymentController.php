<?php

namespace App\Http\Controllers;

use App\Models\MembershipTransaction;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private const METHODS = ['cash', 'bank_transfer', 'gcash', 'card', 'other'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'method' => ['nullable', Rule::in(self::METHODS)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $payments = Payment::with(['transaction.member', 'transaction.level'])
            ->when($filters['method'] ?? null, fn ($query, $method) => $query->where('method', $method))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('transaction', function ($query) use ($search) {
                            $query->where('reference_no', 'like', "%{$search}%")
                                ->orWhereHas('member', function ($query) use ($search) {
                                    $query->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('member_number', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('payments.index', compact('payments', 'filters'));
    }

    public function store(Request $request, MembershipTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'decimal:0,2'],
            'method' => ['required', Rule::in(self::METHODS)],
            'reference' => ['nullable', 'string', 'max:100', Rule::unique('payments', 'reference')],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
        ]);

        DB::transaction(function () use ($transaction, $validated) {
            $transaction = MembershipTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if (! in_array($transaction->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages([
                    'payment' => 'Payments can only be added to pending or approved transactions.',
                ]);
            }

            $amountCents = (int) round((float) $validated['amount'] * 100);
            $dueCents = (int) round((float) $transaction->amount_due * 100);
            $paidCents = (int) round((float) $transaction->payments()->sum('amount') * 100);
            $balanceCents = max(0, $dueCents - $paidCents);

            if ($amountCents > $balanceCents) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment cannot be greater than the remaining transaction balance.',
                ]);
            }

            Payment::create([
                'payment_no' => 'PAY-'.Str::upper((string) Str::ulid()),
                'membership_transaction_id' => $transaction->id,
                'amount' => number_format($amountCents / 100, 2, '.', ''),
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'paid_at' => $validated['paid_at'],
            ]);

            if ($transaction->status === 'pending' && $paidCents + $amountCents >= $dueCents) {
                $transaction->update(['status' => 'paid']);
            }
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Payment recorded. A payment reference number has been assigned.');
    }
}
