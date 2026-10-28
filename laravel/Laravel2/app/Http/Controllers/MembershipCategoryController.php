<?php

namespace App\Http\Controllers;

use App\Models\MembershipCategory;
use App\Models\MembershipTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipCategoryController extends Controller
{
    public function index()
    {
        return view('categories.index', [
            'categories' => MembershipCategory::withCount('members')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        MembershipCategory::create($data);

        return redirect()->route('categories.index')->with('success', 'Membership category added.');
    }

    public function update(Request $request, MembershipCategory $category)
    {
        $data = $request->validate($this->rules($category));
        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Membership category updated.');
    }

    public function destroy(MembershipCategory $category)
    {
        if ($category->members()->exists() || MembershipTransaction::where('membership_category_id', $category->id)->exists()) {
            return back()->withErrors(['category' => 'This category is in use and cannot be deleted.']);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Membership category deleted.');
    }

    private function rules(?MembershipCategory $category = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('membership_categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
