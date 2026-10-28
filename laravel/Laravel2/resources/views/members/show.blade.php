@extends('layouts.app')
@section('title', 'Member profile')
@section('eyebrow', 'MEMBER PROFILE')
@section('heading', $member->full_name)
@section('subtitle', $member->member_number . ' · Joined ' . ($member->joined_at?->format('M j, Y') ?? 'Pending'))
@section('actions')<a class="btn btn-outline" href="{{ route('members.index') }}">← Members</a><a class="btn btn-primary" href="{{ route('members.edit', $member) }}">Edit profile</a>@endsection

@section('content')
<div class="profile-grid">
    <div class="profile-main">
        <section class="panel profile-hero">
            <div class="profile-identity"><span class="profile-avatar">{{ strtoupper(substr($member->first_name, 0, 1).substr($member->last_name, 0, 1)) }}</span><div><div class="eyebrow">{{ $member->member_number }}</div><h2>{{ $member->full_name }}</h2><span class="badge badge-{{ $member->display_status }}">{{ ucfirst($member->display_status) }}</span></div></div>
            <div class="profile-facts"><div><small>Category</small><strong>{{ $member->category->name }}</strong></div><div><small>Level</small><strong>{{ $member->level->name }}</strong></div><div><small>Valid until</small><strong>{{ $member->expires_at?->format('M j, Y') ?? '—' }}</strong></div></div>
        </section>
        <section class="panel detail-panel"><div class="eyebrow">MEMBERSHIP LEVEL</div><h2>{{ $member->level->name }} benefits</h2><p class="note-text">{{ $member->level->benefits ?: 'Benefits to be announced.' }}</p></section>
    </div>

    <aside class="profile-aside">
        <section class="panel detail-panel"><div class="eyebrow">CONTACT DETAILS</div><h2>Member information</h2><dl><div><dt>Email address</dt><dd><a href="mailto:{{ $member->email }}">{{ $member->email }}</a></dd></div><div><dt>Phone number</dt><dd>{{ $member->phone ?: '—' }}</dd></div><div><dt>Address</dt><dd>{{ $member->address ?: '—' }}</dd></div><div><dt>Member since</dt><dd>{{ $member->joined_at?->format('M j, Y') ?? 'Pending' }}</dd></div></dl></section>
        <section class="panel detail-panel"><div class="eyebrow">LEVEL DETAILS</div><h2>{{ $member->level->name }} membership</h2><dl><div><dt>Fee</dt><dd>₱{{ number_format($member->level->fee, 2) }}</dd></div><div><dt>Validity</dt><dd>{{ $member->level->validity_months }} months</dd></div></dl></section>
        @if($member->notes)<section class="panel detail-panel"><div class="eyebrow">INTERNAL NOTES</div><p class="note-text">{{ $member->notes }}</p></section>@endif
        <section class="panel detail-panel"><div class="eyebrow">MEMBER ACTIONS</div><h2>Manage member</h2><p class="muted">Deactivation keeps the member’s profile available.</p>
            @if($member->status === 'inactive')<form method="POST" action="{{ route('members.reactivate', $member) }}">@csrf @method('PATCH')<button class="btn btn-secondary full-width" type="submit">Reactivate member</button></form>
            @else<form method="POST" action="{{ route('members.deactivate', $member) }}" onsubmit="return confirm('Deactivate this member?')">@csrf @method('PATCH')<button class="btn btn-danger-outline full-width" type="submit">Deactivate member</button></form>@endif
            <form method="POST" action="{{ route('members.destroy', $member) }}" class="member-danger-action" onsubmit="return confirm('Permanently delete this member? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline full-width" type="submit">Delete member</button></form>
        </section>
    </aside>
</div>
@endsection
