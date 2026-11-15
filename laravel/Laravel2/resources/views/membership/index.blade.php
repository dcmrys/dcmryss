@extends('layouts.app')

@section('title', 'Membership Applications')
@section('eyebrow', 'APPLICATION REVIEW')
@section('heading', 'Membership Applications')
@section('subtitle', 'Review applications here. Approval creates a pending membership transaction; record payment and approve the transaction to activate the membership.')
@section('actions')
    <span class="count-pill">{{ $pendingCount }} pending</span>
@endsection

@section('content')
<section class="panel">
    <div class="panel-heading">
        <div><h2>Submitted applications</h2><p>Pending applications appear first.</p></div>
        <span class="count-pill">{{ $applications->total() }} total</span>
    </div>
    <div class="table-wrap">
        <table class="application-table">
            <thead>
                <tr><th>Applicant</th><th>Reference</th><th>Category</th><th>Phone</th><th>Submitted</th><th>Application status</th><th>Update status</th></tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td><div class="member-cell"><span><strong>{{ $application->full_name }}</strong><span class="table-secondary">{{ $application->email }}</span></span></div></td>
                        <td class="mono">{{ $application->reference_no }}</td>
                        <td>{{ $application->category?->name ?? '—' }}</td>
                        <td>{{ $application->phone ?: '—' }}</td>
                        <td>{{ $application->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        <td><span class="badge badge-{{ \Illuminate\Support\Str::slug($application->status) }}">{{ ucfirst($application->status) }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('applications.status', $application) }}" class="application-status-form">
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="application-status-{{ $application->id }}">Application status for {{ $application->full_name }}</label>
                                <select id="application-status-{{ $application->id }}" name="status">
                                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $application->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-secondary btn-small" type="submit">Update status</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><span>—</span><h3>No applications yet</h3><p>New applications submitted through the public form will appear here.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($applications->hasPages())
        <div class="pagination">
            <span>Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }}</span>
            <div>
                @if($applications->previousPageUrl())<a href="{{ $applications->previousPageUrl() }}">← Previous</a>@endif
                @if($applications->nextPageUrl())<a href="{{ $applications->nextPageUrl() }}">Next →</a>@endif
            </div>
        </div>
    @endif
</section>
@endsection
