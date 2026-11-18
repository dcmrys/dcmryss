<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function mine(): View
    {
        abort_if(auth()->user()->isAdmin(), 403);

        $member = Member::where('user_id', auth()->id())->first();
        $transactions = null;
        $totalDue = 0.0;
        $totalPaid = 0.0;
        $balance = 0.0;

        if ($member) {
            MembershipTransaction::where('member_id', $member->id)
                ->where('status', 'approved')
                ->whereNotNull('ends_on')
                ->whereDate('ends_on', '<', today())
                ->update(['status' => 'expired']);

            $transactions = MembershipTransaction::with(['level', 'payments' => fn ($query) => $query->latest('paid_at')->latest()])
                ->where('member_id', $member->id)
                ->latest()
                ->paginate(15);

            $totalDue = (float) MembershipTransaction::where('member_id', $member->id)->sum('amount_due');
            $totalPaid = (float) DB::table('payments')
                ->join('membership_transactions', 'payments.membership_transaction_id', '=', 'membership_transactions.id')
                ->where('membership_transactions.member_id', $member->id)
                ->sum('payments.amount');
            $balance = max(0, round($totalDue - $totalPaid, 2));
        }

        return view('transactions.mine', compact('member', 'transactions', 'totalDue', 'totalPaid', 'balance'));
    }

    public function index(Request $request): View
    {
        $this->expireTransactions();

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(MembershipTransaction::STATUSES)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $transactions = MembershipTransaction::with(['member', 'level', 'payments'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('member_number', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = MembershipTransaction::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('transactions.index', compact('transactions', 'filters', 'statusCounts'));
    }

    public function create(): View
    {
        return view('transactions.create', [
            'members' => Member::with('level')
                ->whereNotNull('joined_at')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
            'levels' => MembershipLevel::orderBy('fee')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer', Rule::exists('members', 'id')],
            'membership_level_id' => ['required', 'integer', Rule::exists('membership_levels', 'id')],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $transaction = DB::transaction(function () use ($validated) {
            $member = Member::whereKey($validated['member_id'])->lockForUpdate()->firstOrFail();
            if (! $member->joined_at) {
                throw ValidationException::withMessages([
                    'member_id' => 'Only an established member can receive a renewal transaction.',
                ]);
            }

            if ($member->transactions()->where('type', 'renewal')->whereIn('status', ['pending', 'paid'])->exists()) {
                throw ValidationException::withMessages([
                    'member_id' => 'This member already has a renewal awaiting payment or approval.',
                ]);
            }

            $level = MembershipLevel::findOrFail($validated['membership_level_id']);

            return MembershipTransaction::create([
                'reference_no' => 'TXN-'.Str::upper((string) Str::ulid()),
                'member_id' => $member->id,
                'membership_category_id' => $member->membership_category_id,
                'membership_level_id' => $level->id,
                'type' => 'renewal',
                'status' => (float) $level->fee <= 0 ? 'paid' : 'pending',
                'amount_due' => $level->fee,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Renewal transaction {$transaction->reference_no} created.");
    }

    public function show(MembershipTransaction $transaction): View
    {
        $this->expireTransactions();
        $transaction->load(['member', 'category', 'level', 'payments' => fn ($query) => $query->latest('paid_at')->latest()]);

        return view('transactions.show', compact('transaction'));
    }

    public function approve(MembershipTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $transaction = MembershipTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($transaction->status !== 'paid') {
                throw ValidationException::withMessages([
                    'transaction' => 'A transaction must be fully paid before it can be approved.',
                ]);
            }

            if ($transaction->balance > 0) {
                throw ValidationException::withMessages([
                    'transaction' => 'The recorded payments do not cover the transaction balance.',
                ]);
            }

            $member = Member::whereKey($transaction->member_id)->lockForUpdate()->firstOrFail();
            $level = MembershipLevel::findOrFail($transaction->membership_level_id);
            $today = today();
            $startsOn = $transaction->type === 'renewal'
                && $member->expires_at
                && $member->expires_at->greaterThanOrEqualTo($today)
                    ? $member->expires_at->copy()->addDay()
                    : $today;
            $endsOn = $startsOn->copy()->addMonthsNoOverflow($level->validity_months)->subDay();

            $member->update([
                'membership_category_id' => $transaction->membership_category_id,
                'membership_level_id' => $level->id,
                'status' => 'active',
                'joined_at' => $member->joined_at ?? $startsOn,
                'expires_at' => $endsOn,
            ]);

            $transaction->update([
                'status' => 'approved',
                'starts_on' => $startsOn,
                'ends_on' => $endsOn,
                'processed_at' => now(),
            ]);
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction approved. The membership dates have been updated.');
    }

    public function cancel(MembershipTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $transaction = MembershipTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($transaction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'transaction' => 'Only unpaid pending transactions can be cancelled.',
                ]);
            }

            if ($transaction->payments()->exists()) {
                throw ValidationException::withMessages([
                    'transaction' => 'A transaction with recorded payments cannot be cancelled. Resolve the payment first.',
                ]);
            }

            $transaction->update(['status' => 'cancelled', 'processed_at' => now()]);
        });

        return redirect()->route('transactions.show', $transaction)->with('success', 'Transaction cancelled.');
    }

    public function destroy(MembershipTransaction $transaction): RedirectResponse
    {
        DB::transaction(function () use ($transaction) {
            $lockedTransaction = MembershipTransaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($lockedTransaction->status !== 'cancelled') {
                throw ValidationException::withMessages([
                    'transaction' => 'Only cancelled transactions can be deleted.',
                ]);
            }

            if ($lockedTransaction->payments()->exists()) {
                throw ValidationException::withMessages([
                    'transaction' => 'A transaction with payment records cannot be deleted.',
                ]);
            }

            $lockedTransaction->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Cancelled transaction deleted.');
    }

    private function expireTransactions(): void
    {
        MembershipTransaction::where('status', 'approved')
            ->whereNotNull('ends_on')
            ->whereDate('ends_on', '<', today())
            ->update(['status' => 'expired']);
    }
}
