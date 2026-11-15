@extends('layouts.app')

@section('title', 'My Transactions')
@section('eyebrow', 'MEMBER ACCOUNT')
@section('heading', 'My Transaction History')
@section('subtitle', 'Review your membership applications, renewals, balances, and recorded payments.')

@section('content')
@if(! $member)
    <section class="panel"><div class="empty-state"><h3>No member profile is linked</h3><p>Contact the membership administrator to connect your login with a member profile.</p></div></section>
@else
    <div class="stats-grid member-transaction-stats">
        <article class="stat-card"><div class="stat-label">Total charges</div><div class="stat-value">₱{{ number_format($totalDue, 2) }}</div><div class="stat-foot">Across all transactions</div></article>
        <article class="stat-card"><div class="stat-label">Payments recorded</div><div class="stat-value">₱{{ number_format($totalPaid, 2) }}</div><div class="stat-foot">Receipts on file</div></article>
        <article class="stat-card"><div class="stat-label">Outstanding balance</div><div class="stat-value">₱{{ number_format($balance, 2) }}</div><div class="stat-foot">Remaining across all transactions</div></article>
    </div>

    <section class="panel">
        <div class="panel-heading"><div><h2>{{ $member->full_name }}</h2><p>{{ $member->member_number }} · {{ $transactions->total() }} transaction(s)</p></div></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Reference</th><th>Type</th><th>Level</th><th>Due</th><th>Paid</th><th>Balance</th><th>Status</th><th>Membership period</th><th>Payments</th></tr></thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="mono">{{ $transaction->reference_no }}</td>
                            <td>{{ ucfirst($transaction->type) }}</td>
                            <td>{{ $transaction->level?->name ?? '—' }}</td>
                            <td>₱{{ number_format((float) $transaction->amount_due, 2) }}</td>
                            <td>₱{{ number_format($transaction->paid_amount, 2) }}</td>
                            <td>₱{{ number_format($transaction->balance, 2) }}</td>
                            <td><span class="badge badge-{{ $transaction->display_status }}">{{ ucfirst($transaction->display_status) }}</span></td>
                            <td>{{ $transaction->starts_on?->format('M j, Y') ?? '—' }}{{ $transaction->ends_on ? ' – '.$transaction->ends_on->format('M j, Y') : '' }}</td>
                            <td>
                                @if($transaction->payments->isEmpty())
                                    —
                                @else
                                    <details class="member-payment-details">
                                        <summary>{{ $transaction->payments->count() }} {{ \Illuminate\Support\Str::plural('payment', $transaction->payments->count()) }}</summary>
                                        <ul>
                                            @foreach($transaction->payments as $payment)
                                                <li><strong>{{ $payment->payment_no }}</strong><span>₱{{ number_format((float) $payment->amount, 2) }} · {{ $payment->paid_at?->format('M j, Y') }} · {{ ucwords(str_replace('_', ' ', $payment->method)) }}</span>@if($payment->reference)<span>Receipt: {{ $payment->reference }}</span>@endif</li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9"><div class="empty-state"><h3>No transactions yet</h3><p>Application and renewal transactions will appear here.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="pagination"><span>Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}</span><div>@if($transactions->previousPageUrl())<a href="{{ $transactions->previousPageUrl() }}">← Previous</a>@endif @if($transactions->nextPageUrl())<a href="{{ $transactions->nextPageUrl() }}">Next →</a>@endif</div></div>
        @endif
    </section>
@endif
@endsection
