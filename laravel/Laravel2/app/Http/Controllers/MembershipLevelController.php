<?php

namespace App\Http\Controllers;

use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipLevelController extends Controller
{
    public function index()
    {
        return view('levels.index', [
            'levels' => MembershipLevel::withCount('members')->orderBy('fee')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        MembershipLevel::create($data);

        return redirect()->route('levels.index')->with('success', 'Membership level added.');
    }

    public function update(Request $request, MembershipLevel $level)
    {
        $data = $request->validate($this->rules($level));
        $level->update($data);

        return redirect()->route('levels.index')->with('success', 'Membership level updated.');
    }

    public function destroy(MembershipLevel $level)
    {
        if ($level->members()->exists() || MembershipTransaction::where('membership_level_id', $level->id)->exists()) {
            return back()->withErrors(['level' => 'This level is in use and cannot be deleted.']);
        }

        $level->delete();

        return redirect()->route('levels.index')->with('success', 'Membership level deleted.');
    }

    private function rules(?MembershipLevel $level = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_levels', 'name')->ignore($level?->id)],
            'fee' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'validity_months' => ['required', 'integer', 'between:1,120'],
            'benefits' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
