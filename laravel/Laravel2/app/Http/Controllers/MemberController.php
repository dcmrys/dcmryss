<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:membership_categories,id'],
            'level' => ['nullable', 'integer', 'exists:membership_levels,id'],
            'status' => ['nullable', Rule::in(['pending', 'active', 'inactive', 'expired'])],
        ]);

        $members = Member::with(['category', 'level'])
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
                if ($status === 'expired') {
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
            $level = MembershipLevel::findOrFail($data['membership_level_id']);
            $start = today();
            $member = Member::create($data + [
                'status' => 'active',
                'joined_at' => $start,
                'expires_at' => $start->copy()->addMonthsNoOverflow($level->validity_months)->subDay(),
            ]);
            $member->update(['member_number' => sprintf('MB-%06d', $member->id)]);

            return $member;
        });

        return redirect()->route('members.show', $member)->with('success', 'Member registered with the selected category and level.');
    }

    public function show(Member $member)
    {
        $member->load(['category', 'level']);

        return view('members.show', compact('member'));
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

        if ((int) $data['membership_level_id'] !== $member->membership_level_id) {
            $level = MembershipLevel::findOrFail($data['membership_level_id']);
            $start = today();
            $data['joined_at'] = $member->joined_at ?? $start;
            $data['expires_at'] = $start->copy()->addMonthsNoOverflow($level->validity_months)->subDay();
            if ($member->status === 'pending') {
                $data['status'] = 'active';
            }
        }

        $member->update($data);

        return redirect()->route('members.show', $member)->with('success', 'Member details updated.');
    }

    public function destroy(Member $member)
    {
        if ($member->transactions()->exists()) {
            return back()->withErrors(['member' => 'This member has transaction history and cannot be deleted. Deactivate them instead.']);
        }

        $member->delete();

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
            'email' => ['required', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member?->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:1000'],
            'membership_category_id' => ['required', 'exists:membership_categories,id'],
            'membership_level_id' => ['required', 'exists:membership_levels,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
