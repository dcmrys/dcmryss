@extends('layouts.app')
@section('title', 'Member profile')
@section('eyebrow', 'MEMBER PROFILE')
@section('heading', $member->full_name)
@section('subtitle', $member->member_number . ' · Joined ' . ($member->joined_at?->format('M j, Y') ?? 'Pending'))
@section('actions')<a class="btn btn-outline" href="{{ route('members.index') }}">← Members</a>@if(auth()->user()->isAdmin())<a class="btn btn-primary" href="{{ route('members.edit', $member) }}">Edit profile</a>@endif
@endsection

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
        @if(auth()->user()->isAdmin() && $member->notes)<section class="panel detail-panel"><div class="eyebrow">INTERNAL NOTES</div><p class="note-text">{{ $member->notes }}</p></section>@endif
        @if(auth()->user()->isAdmin())<section class="panel detail-panel"><div class="eyebrow">MEMBER ACTIONS</div><h2>Manage member</h2><p class="muted">Deactivation keeps the member’s profile available.</p>
            @if($member->status === 'inactive')<form method="POST" action="{{ route('members.reactivate', $member) }}">@csrf @method('PATCH')<button class="btn btn-secondary full-width" type="submit">Reactivate member</button></form>
            @else<form method="POST" action="{{ route('members.deactivate', $member) }}" onsubmit="return confirm('Deactivate this member?')">@csrf @method('PATCH')<button class="btn btn-danger-outline full-width" type="submit">Deactivate member</button></form>@endif
            <form method="POST" action="{{ route('members.destroy', $member) }}" class="member-danger-action" onsubmit="return confirm('Permanently delete this member? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline full-width" type="submit">Delete member</button></form>
        </section>@endif
    </aside>
</div>

@if($canViewTransactionHistory)
    <section class="panel member-transaction-history">
        <div class="panel-heading">
            <div><h2>Transaction history</h2><p>Membership applications, renewals, and recorded payments.</p></div>
            <span class="count-pill">{{ $member->transactions->count() }} total</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Type</th><th>Level</th><th>Due</th><th>Paid</th><th>Balance</th><th>Status</th><th>Period</th></tr></thead>
                <tbody>
                    @forelse($member->transactions as $transaction)
                        <tr>
                            <td class="mono">
                                @if(auth()->user()->isAdmin())
                                    <a class="table-primary" href="{{ route('transactions.show', $transaction) }}">{{ $transaction->reference_no }}</a>
                                @else
                                    {{ $transaction->reference_no }}
                                @endif
                            </td>
                            <td>{{ ucfirst($transaction->type) }}</td>
                            <td>{{ $transaction->level?->name ?? '—' }}</td>
                            <td>₱{{ number_format((float) $transaction->amount_due, 2) }}</td>
                            <td>₱{{ number_format($transaction->paid_amount, 2) }}</td>
                            <td>₱{{ number_format($transaction->balance, 2) }}</td>
                            <td><span class="badge badge-{{ $transaction->display_status }}">{{ ucfirst($transaction->display_status) }}</span></td>
                            <td>{{ $transaction->starts_on?->format('M j, Y') ?? '—' }}{{ $transaction->ends_on ? ' – '.$transaction->ends_on->format('M j, Y') : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="empty-state"><h3>No transactions yet</h3><p>Application or renewal transactions will appear here.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
