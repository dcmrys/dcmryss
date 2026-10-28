@extends('layouts.app')
@section('title', 'Membership Levels')
@section('eyebrow', 'MEMBERSHIP SETTINGS')
@section('heading', 'Membership Levels')
@section('subtitle', 'Set each level’s fee, validity period, and benefits. Members choose a level during registration.')

@section('content')
<div class="catalog-grid">
    <section class="panel catalog-section">
        <div class="panel-heading">
            <div><h2>Provided levels</h2><p>Update plan details as your membership program changes.</p></div>
            <span class="count-pill">{{ $levels->count() }} total</span>
        </div>
        <div class="catalog-list">
            @forelse($levels as $level)
                <div class="catalog-item">
                    <form method="POST" action="{{ route('levels.update', $level) }}" class="catalog-form">
                        @csrf @method('PUT')
                        <div class="catalog-item-top"><span class="level-symbol">✦</span><span class="catalog-meta">{{ $level->members_count }} {{ Str::plural('member', $level->members_count) }}</span></div>
                        <label class="field"><span>Level name</span><input name="name" value="{{ $level->name }}" required maxlength="100"></label>
                        <div class="form-grid">
                            <label class="field"><span>Fee (₱)</span><input name="fee" type="number" min="0" max="99999999.99" step="0.01" value="{{ $level->fee }}" required></label>
                            <label class="field"><span>Validity (months)</span><input name="validity_months" type="number" min="1" max="120" step="1" value="{{ $level->validity_months }}" required></label>
                        </div>
                        <label class="field"><span>Benefits</span><textarea name="benefits" rows="4" maxlength="2000" placeholder="One benefit per line">{{ $level->benefits }}</textarea></label>
                        <div class="form-actions"><button class="btn btn-secondary btn-small" type="submit">Save changes</button></div>
                    </form>
                    @if($level->members_count === 0)
                        <form method="POST" action="{{ route('levels.destroy', $level) }}" class="catalog-delete" onsubmit="return confirm('Delete this membership level?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="delete-link">Delete level</button>
                        </form>
                    @else
                        <p class="catalog-use-note">In use by members · cannot be deleted</p>
                    @endif
                </div>
            @empty
                <p class="empty-card">No levels yet. Add one using the form.</p>
            @endforelse
        </div>
    </section>

    <section class="panel form-panel">
        <div class="section-heading"><span class="section-icon">＋</span><div><h2>Add a level</h2><p>Provide its price, duration, and included benefits.</p></div></div>
        <form method="POST" action="{{ route('levels.store') }}" class="stacked-form">
            @csrf
            <label class="field"><span>Level name <em>*</em></span><input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Diamond"></label>
            <div class="form-grid">
                <label class="field"><span>Fee (₱) <em>*</em></span><input name="fee" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('fee') }}" required></label>
                <label class="field"><span>Validity (months) <em>*</em></span><input name="validity_months" type="number" min="1" max="120" step="1" value="{{ old('validity_months') }}" required></label>
            </div>
            <label class="field"><span>Benefits</span><textarea name="benefits" rows="5" maxlength="2000" placeholder="One benefit per line">{{ old('benefits') }}</textarea></label>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Add level</button></div>
        </form>
        <p class="catalog-use-note">Edits to a level affect new registrations. Existing membership expiry dates stay as recorded.</p>
    </section>
</div>
@endsection
