@extends('layouts.app')
@php($editing = $member->exists)
@section('title', $editing ? 'Edit member' : 'Add member')
@section('eyebrow', $editing ? 'UPDATE PROFILE' : 'NEW REGISTRATION')
@section('heading', $editing ? 'Edit member' : 'Add a member')
@section('subtitle', $editing ? 'Update personal details, category, or membership level.' : 'Choose a category and level while registering a member.')
@section('actions')<a class="btn btn-outline" href="{{ $editing ? route('members.show', $member) : route('members.index') }}">← Back</a>@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('members.update', $member) : route('members.store') }}" class="form-layout">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="form-main">
        <section class="panel form-panel">
            <div class="section-heading"><span class="section-icon">01</span><div><h2>Personal information</h2><p>Basic details used for the member profile.</p></div></div>
            <div class="form-grid">
                <label class="field"><span>First name <em>*</em></span><input name="first_name" value="{{ old('first_name', $member->first_name) }}" required maxlength="100" autocomplete="given-name"></label>
                <label class="field"><span>Last name <em>*</em></span><input name="last_name" value="{{ old('last_name', $member->last_name) }}" required maxlength="100" autocomplete="family-name"></label>
                <label class="field"><span>Email address <em>*</em></span><input name="email" type="email" value="{{ old('email', $member->email) }}" required autocomplete="email"></label>
                <label class="field"><span>Phone number</span><input name="phone" value="{{ old('phone', $member->phone) }}" autocomplete="tel"></label>
                <label class="field full"><span>Address</span><textarea name="address" rows="2">{{ old('address', $member->address) }}</textarea></label>
            </div>
        </section>

        <section class="panel form-panel">
            <div class="section-heading"><span class="section-icon">02</span><div><h2>Membership details</h2><p>Select one of the provided categories and levels.</p></div></div>
            <div class="form-grid">
                <label class="field"><span>Category <em>*</em></span><select name="membership_category_id" required><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('membership_category_id', $member->membership_category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                <label class="field"><span>Level <em>*</em></span><select name="membership_level_id" required><option value="">Select level</option>@foreach($levels as $level)<option value="{{ $level->id }}" @selected(old('membership_level_id', $member->membership_level_id) == $level->id)>{{ $level->name }} · ₱{{ number_format($level->fee, 2) }} / {{ $level->validity_months }} months</option>@endforeach</select></label>
                <label class="field full"><span>Internal notes</span><textarea name="notes" rows="3" placeholder="Optional context for your team">{{ old('notes', $member->notes) }}</textarea></label>
            </div>
        </section>

        <div class="form-actions"><a class="btn btn-outline" href="{{ $editing ? route('members.show', $member) : route('members.index') }}">Cancel</a><button class="btn btn-primary" type="submit">{{ $editing ? 'Save changes' : 'Register member' }} <span>→</span></button></div>
    </div>

    <aside class="form-aside">
        <div class="info-card">
            <div class="info-card-icon">✦</div>
            <h3>Membership levels</h3>
            <p>{{ $editing ? 'Changing a level starts its new validity period today.' : 'The selected level starts today when the member is registered.' }} Fees are shown for reference.</p>
            @foreach($levels as $level)
                <div class="plan-option">
                    <div><strong>{{ $level->name }}</strong><span>₱{{ number_format($level->fee, 2) }} · {{ $level->validity_months }} months</span></div>
                    <p>{{ $level->benefits ?: 'Benefits to be announced.' }}</p>
                </div>
            @endforeach
        </div>
    </aside>
</form>
@endsection
