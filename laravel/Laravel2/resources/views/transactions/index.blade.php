@extends('layouts.app')

@section('title', 'Transactions')
@section('heading', 'Transaction Processing')
@section('subtitle', 'Track application fees, renewal charges, payments, approvals, and membership periods.')
@section('actions')
    <a class="btn btn-outline" href="{{ route('payments.index') }}">Payment register</a>
    <a class="btn btn-primary" href="{{ route('transactions.create') }}">Start renewal</a>
@endsection

@section('content')
<div class="stats-grid transaction-stats">
    @foreach(['pending' => 'Pending', 'paid' => 'Paid', 'approved' => 'Approved', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $status => $label)
        <article class="stat-card"><div class="stat-label">{{ $label }}</div><div class="stat-value">{{ $statusCounts[$status] ?? 0 }}</div><div class="stat-foot">Transactions</div></article>
    @endforeach
</div>

<section class="panel">
    <div class="panel-heading">
        <div><h2>Membership transactions</h2><p>Track applications, registrations, and renewals. Approve fully paid transactions to apply membership dates.</p></div>
        <span class="count-pill">{{ $transactions->total() }} total</span>
    </div>
    <form method="GET" action="{{ route('transactions.index') }}" class="filter-bar">
        <input class="search-field" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, member, or member ID">
        <select name="status" aria-label="Filter transactions by status">
            <option value="">All statuses</option>
            @foreach(\App\Models\MembershipTransaction::STATUSES as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary btn-small" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'status']))<a class="clear-link" href="{{ route('transactions.index') }}">Clear</a>@endif
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Transaction reference</th><th>Member</th><th>Type</th><th>Level</th><th>Due</th><th>Paid</th><th>Balance</th><th>Status</th><th>Created</th><th></th></tr></thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="mono"><a class="table-primary" href="{{ route('transactions.show', $transaction) }}">{{ $transaction->reference_no }}</a></td>
                        <td><a class="table-primary" href="{{ route('members.show', $transaction->member) }}">{{ $transaction->member->full_name }}</a><span class="table-secondary">{{ $transaction->member->member_number }}</span></td>
                        <td>{{ ucfirst($transaction->type) }}</td>
                        <td>{{ $transaction->level->name }}</td>
                        <td>₱{{ number_format((float) $transaction->amount_due, 2) }}</td>
                        <td>₱{{ number_format($transaction->paid_amount, 2) }}</td>
                        <td>₱{{ number_format($transaction->balance, 2) }}</td>
                        <td><span class="badge badge-{{ $transaction->display_status }}">{{ ucfirst($transaction->display_status) }}</span></td>
                        <td>{{ $transaction->created_at?->format('M j, Y') }}</td>
                        <td><div class="transaction-row-actions"><a class="btn btn-outline btn-small" href="{{ route('transactions.show', $transaction) }}">View</a>@if($transaction->status === 'cancelled' && $transaction->payments->isEmpty())<form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Permanently delete this cancelled transaction? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-small" type="submit">Delete</button></form>@endif</div></td>
                    </tr>
                @empty
                    <tr><td colspan="10"><div class="empty-state"><span>₱</span><h3>No transactions yet</h3><p>Approving a membership application creates its initial transaction. Create renewals from this page.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
        <div class="pagination">
            <span>Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}</span>
            <div>@if($transactions->previousPageUrl())<a href="{{ $transactions->previousPageUrl() }}">← Previous</a>@endif @if($transactions->nextPageUrl())<a href="{{ $transactions->nextPageUrl() }}">Next →</a>@endif</div>
        </div>
    @endif
</section>
@endsection
