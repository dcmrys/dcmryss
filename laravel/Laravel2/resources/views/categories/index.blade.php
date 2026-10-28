@extends('layouts.app')
@section('title', 'Membership Categories')
@section('eyebrow', 'MEMBERSHIP SETTINGS')
@section('heading', 'Membership Categories')
@section('subtitle', 'Organize members by category. Changes here appear in the member registration form.')

@section('content')
<div class="catalog-grid">
    <section class="panel catalog-section">
        <div class="panel-heading">
            <div><h2>Categories</h2><p>Edit the name or description of each category.</p></div>
            <span class="count-pill">{{ $categories->count() }} total</span>
        </div>
        <div class="catalog-list">
            @forelse($categories as $category)
                <div class="catalog-item">
                    <form method="POST" action="{{ route('categories.update', $category) }}" class="catalog-form">
                        @csrf @method('PUT')
                        <div class="catalog-item-top"><span class="catalog-symbol">{{ strtoupper(substr($category->name, 0, 1)) }}</span><span class="catalog-meta">{{ $category->members_count }} {{ Str::plural('member', $category->members_count) }}</span></div>
                        <label class="field"><span>Category name</span><input name="name" value="{{ $category->name }}" required maxlength="100"></label>
                        <label class="field"><span>Description</span><textarea name="description" rows="2" maxlength="1000" placeholder="Optional description">{{ $category->description }}</textarea></label>
                        <div class="form-actions"><button class="btn btn-secondary btn-small" type="submit">Save changes</button></div>
                    </form>
                    @if($category->members_count === 0)
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" class="catalog-delete" onsubmit="return confirm('Delete this membership category?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="delete-link">Delete category</button>
                        </form>
                    @else
                        <p class="catalog-use-note">In use by members · cannot be deleted</p>
                    @endif
                </div>
            @empty
                <p class="empty-card">No categories yet. Add one using the form.</p>
            @endforelse
        </div>
    </section>

    <section class="panel form-panel">
        <div class="section-heading"><span class="section-icon">＋</span><div><h2>Add a category</h2><p>Give members another category to choose from.</p></div></div>
        <form method="POST" action="{{ route('categories.store') }}" class="stacked-form">
            @csrf
            <label class="field"><span>Category name <em>*</em></span><input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Alumni"></label>
            <label class="field"><span>Description</span><textarea name="description" rows="3" maxlength="1000" placeholder="Optional description">{{ old('description') }}</textarea></label>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Add category</button></div>
        </form>
    </section>
</div>
@endsection
