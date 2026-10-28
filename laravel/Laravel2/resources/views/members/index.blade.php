@extends('layouts.app')
@section('title', 'Members')
@section('eyebrow', 'MEMBER DIRECTORY')
@section('heading', 'Members')
@section('subtitle', 'Find, review, and manage every member profile.')
@section('actions')<a class="btn btn-primary" href="{{ route('members.create') }}"><span>＋</span> Add member</a>@endsection

@section('content')
<section class="panel">
    <form method="GET" action="{{ route('members.index') }}" class="filter-bar">
        <label class="search-field"><span>⌕</span><input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, email, or member ID" aria-label="Search members"></label>
        <select name="category" aria-label="Filter by category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select>
        <select name="level" aria-label="Filter by level"><option value="">All levels</option>@foreach($levels as $level)<option value="{{ $level->id }}" @selected(($filters['level'] ?? '') == $level->id)>{{ $level->name }}</option>@endforeach</select>
        <select name="status" aria-label="Filter by status"><option value="">All statuses</option>@foreach(['pending', 'active', 'expired', 'inactive'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if(request()->hasAny(['search', 'category', 'level', 'status']))<a class="clear-link" href="{{ route('members.index') }}">Clear</a>@endif
    </form>
    <div class="table-wrap"><table><thead><tr><th>Member</th><th>Member ID</th><th>Category</th><th>Level</th><th>Status</th><th>Expires</th><th></th></tr></thead><tbody>
        @forelse($members as $member)
            <tr><td><div class="member-cell"><span class="member-avatar">{{ strtoupper(substr($member->first_name, 0, 1).substr($member->last_name, 0, 1)) }}</span><span><a class="table-primary" href="{{ route('members.show', $member) }}">{{ $member->full_name }}</a><span class="table-secondary">{{ $member->email }}</span></span></div></td><td class="mono">{{ $member->member_number }}</td><td>{{ $member->category->name }}</td><td>{{ $member->level->name }}</td><td><span class="badge badge-{{ $member->display_status }}">{{ ucfirst($member->display_status) }}</span></td><td>{{ $member->expires_at?->format('M j, Y') ?? '—' }}</td><td><div class="member-row-actions"><a class="btn btn-outline btn-small" href="{{ route('members.edit', $member) }}" aria-label="Edit {{ $member->full_name }}">Edit</a><form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Permanently delete this member? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-small" type="submit" aria-label="Delete {{ $member->full_name }}">Delete</button></form></div></td></tr>
        @empty
            <tr><td colspan="7"><div class="empty-state"><span>♙</span><h3>No members found</h3><p>Try different filters or register a new member.</p><a class="btn btn-primary" href="{{ route('members.create') }}">Add member</a></div></td></tr>
        @endforelse
    </tbody></table></div>
    @if($members->hasPages())<div class="pagination"><span>Showing {{ $members->firstItem() }}–{{ $members->lastItem() }} of {{ $members->total() }}</span><div>@if($members->previousPageUrl())<a href="{{ $members->previousPageUrl() }}">← Previous</a>@endif @if($members->nextPageUrl())<a href="{{ $members->nextPageUrl() }}">Next →</a>@endif</div></div>@endif
</section>
@endsection
