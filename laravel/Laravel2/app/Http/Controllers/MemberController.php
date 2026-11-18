<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:membership_categories,id'],
            'level' => ['nullable', 'integer', 'exists:membership_levels,id'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'active', 'inactive', 'expired'])],
        ]);

        $members = Member::with(['category', 'level', 'applicationTransaction.application'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                foreach (preg_split('/\s+/', trim($search)) as $term) {
                    $query->where(function ($query) use ($term) {
                        $query->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('member_number', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
                }
            })
            ->when($filters['category'] ?? null, fn ($query, $id) => $query->where('membership_category_id', $id))
            ->when($filters['level'] ?? null, fn ($query, $id) => $query->where('membership_level_id', $id))
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status === 'approved') {
                    $query->where('status', 'pending')
                        ->whereHas('applicationTransaction.application', fn ($application) => $application->where('status', 'approved'));
                } elseif ($status === 'pending') {
                    $query->where('status', 'pending')
                        ->whereDoesntHave('applicationTransaction.application', fn ($application) => $application->where('status', 'approved'));
                } elseif ($status === 'expired') {
                    $query->where('status', 'active')->whereDate('expires_at', '<', today());
                } elseif ($status === 'active') {
                    $query->where('status', 'active')->whereDate('expires_at', '>=', today());
                } else {
                    $query->where('status', $status);
                }
            })
            ->latest()->paginate(12)->withQueryString();

        return view('members.index', [
            'members' => $members,
            'categories' => MembershipCategory::orderBy('name')->get(),
            'levels' => MembershipLevel::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('members.form', [
            'member' => new Member(),
            'categories' => MembershipCategory::orderBy('name')->get(),
            'levels' => MembershipLevel::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedMember($request);

        $member = DB::transaction(function () use ($data) {
            $password = $data['password'];
            unset($data['password']);
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'password' => $password,
                'role' => 'member',
            ]);
            $level = MembershipLevel::findOrFail($data['membership_level_id']);
            $start = today();
            $member = Member::create($data + [
                'user_id' => $user->id,
                'status' => 'active',
                'joined_at' => $start,
                'expires_at' => $start->copy()->addMonthsNoOverflow($level->validity_months)->subDay(),
            ]);
            $member->update(['member_number' => sprintf('MB-%06d', $member->id)]);

            MembershipTransaction::create([
                'reference_no' => 'TXN-'.Str::upper((string) Str::ulid()),
                'member_id' => $member->id,
                'membership_category_id' => $member->membership_category_id,
                'membership_level_id' => $level->id,
                'type' => 'registration',
                'status' => 'approved',
                'amount_due' => $level->fee,
                'starts_on' => $start,
                'ends_on' => $member->expires_at,
                'processed_at' => now(),
                'notes' => 'Membership registered by an administrator.',
            ]);

            return $member;
        });

        $transaction = $member->transactions()->latest()->first();

        return redirect()->route('members.show', $member)->with(
            'success', "Member registered. Transaction {$transaction->reference_no} was created."
        );
    }

    public function show(Member $member)
    {
        $member->load(['category', 'level']);
        $canViewTransactionHistory = auth()->user()->isAdmin() || auth()->id() === $member->user_id;

        if ($canViewTransactionHistory) {
            $member->load(['transactions' => fn ($query) => $query->with(['level', 'payments'])->latest()]);
        }

        return view('members.show', compact('member', 'canViewTransactionHistory'));
    }

    public function edit(Member $member)
    {
        return view('members.form', [
            'member' => $member,
            'categories' => MembershipCategory::orderBy('name')->get(),
            'levels' => MembershipLevel::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $data = $this->validatedMember($request, $member);
        $password = $data['password'] ?? null;
        unset($data['password']);
        $requestedLevelId = (int) $data['membership_level_id'];
        $levelChanged = $requestedLevelId !== (int) $member->membership_level_id;
        $renewalTransaction = null;

        DB::transaction(function () use ($member, $data, $password, $requestedLevelId, $levelChanged, &$renewalTransaction) {
            $lockedMember = Member::whereKey($member->id)->lockForUpdate()->firstOrFail();
            $memberData = $data;

            if ($levelChanged) {
                $level = MembershipLevel::findOrFail($requestedLevelId);

                if ($lockedMember->joined_at) {
                    if ($lockedMember->transactions()->where('type', 'renewal')->whereIn('status', ['pending', 'paid'])->exists()) {
                        throw ValidationException::withMessages([
                            'membership_level_id' => 'This member already has a renewal awaiting payment or approval.',
                        ]);
                    }

                    $memberData['membership_level_id'] = $lockedMember->membership_level_id;
                    $renewalTransaction = MembershipTransaction::create([
                        'reference_no' => 'TXN-'.Str::upper((string) Str::ulid()),
                        'member_id' => $lockedMember->id,
                        'membership_category_id' => $memberData['membership_category_id'],
                        'membership_level_id' => $level->id,
                        'type' => 'renewal',
                        'status' => (float) $level->fee <= 0 ? 'paid' : 'pending',
                        'amount_due' => $level->fee,
                        'notes' => 'Renewal transaction created while editing the member profile.',
                    ]);
                } else {
                    $applicationTransaction = $lockedMember->transactions()
                        ->where('type', 'application')
                        ->whereIn('status', ['pending', 'paid'])
                        ->lockForUpdate()
                        ->first();

                    if (! $applicationTransaction) {
                        throw ValidationException::withMessages([
                            'membership_level_id' => 'A pending member without an application transaction cannot change level here.',
                        ]);
                    }

                    if ($applicationTransaction->payments()->exists()) {
                        throw ValidationException::withMessages([
                            'membership_level_id' => 'The application level cannot change after a payment is recorded.',
                        ]);
                    }

                    $applicationTransaction->update([
                        'membership_category_id' => $memberData['membership_category_id'],
                        'membership_level_id' => $level->id,
                        'amount_due' => $level->fee,
                        'status' => (float) $level->fee <= 0 ? 'paid' : 'pending',
                    ]);
                }
            }

            $lockedMember->update($memberData);
            if ($lockedMember->user) {
                $account = [
                    'name' => $lockedMember->full_name,
                    'email' => $lockedMember->email,
                ];
                if ($password) {
                    $account['password'] = $password;
                }
                $lockedMember->user->update($account);
            } elseif ($password) {
                $user = User::create([
                    'name' => $lockedMember->full_name,
                    'email' => $lockedMember->email,
                    'password' => $password,
                    'role' => 'member',
                ]);
                $lockedMember->update(['user_id' => $user->id]);
            }
        });

        $message = $renewalTransaction
            ? "Member details updated. Renewal transaction {$renewalTransaction->reference_no} is pending payment."
            : 'Member details updated.';

        return redirect()->route('members.show', $member)->with('success', $message);
    }

    public function destroy(Member $member)
    {
        if ($member->transactions()->exists()) {
            return back()->withErrors(['member' => 'Delete cancelled transactions without payments first. Other transaction and payment history must be retained.']);
        }

        DB::transaction(function () use ($member) {
            $user = $member->user;
            $member->delete();
            $user?->delete();
        });

        return redirect()->route('members.index')->with('success', 'Member deleted.');
    }

    public function deactivate(Member $member)
    {
        $member->update(['status' => 'inactive']);

        return back()->with('success', 'Member deactivated. Their profile remains available.');
    }

    public function reactivate(Member $member)
    {
        $member->update(['status' => $member->joined_at ? 'active' : 'pending']);

        return back()->with('success', 'Member reactivated.');
    }

    private function validatedMember(Request $request, ?Member $member = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('members', 'email')->ignore($member?->id),
                Rule::unique('users', 'email')->ignore($member?->user_id),
            ],
            'password' => [$member ? 'nullable' : 'required', 'string', 'min:8', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:1000'],
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
