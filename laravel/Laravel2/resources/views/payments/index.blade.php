@extends('layouts.app')

@section('title', 'Payments')
@section('eyebrow', 'MEMBERSHIP FINANCE')
@section('heading', 'Payment Register')
@section('subtitle', 'Review all recorded membership payments and their transaction references.')
@section('actions')<a class="btn btn-outline" href="{{ route('transactions.index') }}">← Transactions</a>@endsection

@section('content')
<section class="panel">
    <div class="panel-heading"><div><h2>Recorded payments</h2><p>Payments are immutable records linked to membership transactions.</p></div><span class="count-pill">{{ $payments->total() }} total</span></div>
    <form method="GET" action="{{ route('payments.index') }}" class="filter-bar">
        <input class="search-field" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Payment reference, transaction, or member">
        <select name="method" aria-label="Filter payments by method">
            <option value="">All methods</option>
            @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'gcash' => 'GCash', 'card' => 'Card', 'other' => 'Other'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['method'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary btn-small" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'method']))<a class="clear-link" href="{{ route('payments.index') }}">Clear</a>@endif
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Payment number</th><th>Transaction</th><th>Member</th><th>Method</th><th>Receipt reference</th><th>Paid on</th><th>Amount</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td class="mono">{{ $payment->payment_no }}</td>
                        <td class="mono"><a class="table-primary" href="{{ route('transactions.show', $payment->transaction) }}">{{ $payment->transaction->reference_no }}</a></td>
                        <td><a class="table-primary" href="{{ route('members.show', $payment->transaction->member) }}">{{ $payment->transaction->member->full_name }}</a></td>
                        <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
                        <td>{{ $payment->reference ?: '—' }}</td>
                        <td>{{ $payment->paid_at?->format('M j, Y') }}</td>
                        <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><span>₱</span><h3>No payments yet</h3><p>Recorded payments will appear here with their transaction and member details.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="pagination"><span>Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ $payments->total() }}</span><div>@if($payments->previousPageUrl())<a href="{{ $payments->previousPageUrl() }}">← Previous</a>@endif @if($payments->nextPageUrl())<a href="{{ $payments->nextPageUrl() }}">Next →</a>@endif</div></div>
    @endif
</section>
@endsection
