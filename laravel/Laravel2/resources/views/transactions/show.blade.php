@extends('layouts.app')

@section('title', 'Transaction '.$transaction->reference_no)
@section('heading')
    <span class="transaction-reference-heading">{{ $transaction->reference_no }}</span>
@endsection
@section('subtitle', ucfirst($transaction->type).' transaction for '.$transaction->member->full_name)
@section('actions')
    <a class="btn btn-outline" href="{{ route('transactions.index') }}">← Transactions</a>
    <a class="btn btn-outline" href="{{ route('members.show', $transaction->member) }}">Member profile</a>
@endsection

@section('content')
<div class="transaction-detail-grid">
    <section class="panel detail-panel">
        <div class="transaction-status-line"><span class="badge badge-{{ $transaction->display_status }}">{{ ucfirst($transaction->display_status) }}</span><span class="table-secondary">Created {{ $transaction->created_at?->format('M j, Y g:i A') }}</span></div>
        <dl>
            <div><dt>Member</dt><dd><a href="{{ route('members.show', $transaction->member) }}">{{ $transaction->member->full_name }} · {{ $transaction->member->member_number }}</a></dd></div>
            <div><dt>Category</dt><dd>{{ $transaction->category->name }}</dd></div>
            <div><dt>Level</dt><dd>{{ $transaction->level->name }}</dd></div>
            <div><dt>Transaction type</dt><dd>{{ ucfirst($transaction->type) }}</dd></div>
            <div><dt>Amount due</dt><dd>₱{{ number_format((float) $transaction->amount_due, 2) }}</dd></div>
            <div><dt>Paid</dt><dd>₱{{ number_format($transaction->paid_amount, 2) }}</dd></div>
            <div><dt>Balance</dt><dd>₱{{ number_format($transaction->balance, 2) }}</dd></div>
            <div><dt>Membership period</dt><dd>{{ $transaction->starts_on?->format('M j, Y') ?? 'Not activated' }}{{ $transaction->ends_on ? ' – '.$transaction->ends_on->format('M j, Y') : '' }}</dd></div>
            <div><dt>Processed</dt><dd>{{ $transaction->processed_at?->format('M j, Y g:i A') ?? 'Not processed' }}</dd></div>
            @if($transaction->notes)<div><dt>Notes</dt><dd>{{ $transaction->notes }}</dd></div>@endif
        </dl>
        @if($transaction->status === 'paid')
            <form method="POST" action="{{ route('transactions.approve', $transaction) }}" class="transaction-action-form" onsubmit="return confirm('Approve this fully paid transaction and apply its membership dates?')">
                @csrf @method('PATCH')
                <button class="btn btn-primary" type="submit">Approve and apply membership</button>
            </form>
        @elseif($transaction->status === 'pending' && $transaction->payments->isEmpty())
            <form method="POST" action="{{ route('transactions.cancel', $transaction) }}" class="transaction-action-form" onsubmit="return confirm('Cancel this transaction?')">
                @csrf @method('PATCH')
                <button class="btn btn-danger-outline" type="submit">Cancel transaction</button>
            </form>
        @endif
        @if($transaction->status === 'cancelled' && $transaction->payments->isEmpty())
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="transaction-action-form" onsubmit="return confirm('Permanently delete this cancelled transaction? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="btn btn-danger-outline" type="submit">Delete transaction</button>
            </form>
        @endif
    </section>

    @if(in_array($transaction->status, ['pending', 'approved'], true) && $transaction->balance > 0)
        <section class="panel form-panel">
            <div class="section-heading"><span class="section-icon">₱</span><div><h2>Record a payment</h2><p>Remaining balance: ₱{{ number_format($transaction->balance, 2) }}.</p></div></div>
            <form method="POST" action="{{ route('transactions.payments.store', $transaction) }}" class="stacked-form">
                @csrf
                <label class="field"><span>Payment amount (₱) <em>*</em></span><input type="number" name="amount" min="0.01" max="{{ number_format($transaction->balance, 2, '.', '') }}" step="0.01" value="{{ old('amount', number_format($transaction->balance, 2, '.', '')) }}" required></label>
                <label class="field"><span>Payment method <em>*</em></span><select name="method" required>
                    <option value="">Choose a method</option>
                    @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'gcash' => 'GCash', 'card' => 'Card', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('method') === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
                <label class="field"><span>External receipt/reference</span><input name="reference" value="{{ old('reference') }}" maxlength="100" placeholder="Optional — generated if blank"></label>
                <label class="field"><span>Payment date <em>*</em></span><input type="date" name="paid_at" value="{{ old('paid_at', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required></label>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Record payment</button></div>
            </form>
        </section>
    @endif
</div>

<section class="panel transaction-payments">
    <div class="panel-heading"><div><h2>Payment records</h2><p>Each payment receives a unique payment reference.</p></div><span class="count-pill">{{ $transaction->payments->count() }} total</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Payment number</th><th>Paid on</th><th>Method</th><th>Receipt reference</th><th>Amount</th></tr></thead>
            <tbody>
                @forelse($transaction->payments as $payment)
                    <tr><td class="mono">{{ $payment->payment_no }}</td><td>{{ $payment->paid_at?->format('M j, Y') }}</td><td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td><td>{{ $payment->reference ?: '—' }}</td><td>₱{{ number_format((float) $payment->amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state"><h3>No payments recorded</h3><p>Record payment against the remaining balance to move this transaction to Paid.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
