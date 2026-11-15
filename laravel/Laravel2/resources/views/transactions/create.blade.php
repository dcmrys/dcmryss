@extends('layouts.app')

@section('title', 'Start Membership Renewal')
@section('eyebrow', 'MEMBERSHIP FINANCE')
@section('heading', 'Start a renewal')
@section('subtitle', 'Create a pending charge. Record payment, then approve it to extend the member’s dates.')
@section('actions')<a class="btn btn-outline" href="{{ route('transactions.index') }}">← Transactions</a>@endsection

@section('content')
<section class="panel form-panel">
    <div class="section-heading"><span class="section-icon">↻</span><div><h2>Renewal details</h2><p>The new period starts after the current membership expires, or today if it is already expired.</p></div></div>
    @if($members->isEmpty())
        <div class="empty-state"><h3>No established members available</h3><p>A member must have an active membership record before a renewal can be created.</p></div>
    @else
        <form method="POST" action="{{ route('transactions.store') }}" class="stacked-form">
            @csrf
            <label class="field"><span>Member <em>*</em></span>
                <select name="member_id" required>
                    <option value="">Choose a member</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->member_number }} — {{ $member->full_name }} · {{ $member->level?->name ?? 'No level' }}{{ $member->expires_at ? ' · expires '.$member->expires_at->format('M j, Y') : '' }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field"><span>Renewal level <em>*</em></span>
                <select name="membership_level_id" required>
                    <option value="">Choose a level</option>
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}" @selected((string) old('membership_level_id') === (string) $level->id)>{{ $level->name }} — ₱{{ number_format((float) $level->fee, 2) }} / {{ $level->validity_months }} months</option>
                    @endforeach
                </select>
            </label>
            <label class="field"><span>Internal notes</span><textarea name="notes" rows="4" maxlength="2000" placeholder="Optional notes for this renewal">{{ old('notes') }}</textarea></label>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Create renewal transaction</button></div>
        </form>
    @endif
</section>
@endsection
