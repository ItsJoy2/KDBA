
@extends('layouts.app')

@section('title', 'My Assistants')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Assistants</h4>
            <p class="text-muted mb-0">
                Assigned lawyer assistants and their details.
            </p>
        </div>
        <span class="badge bg-primary">
            Total: {{ $assistants->total() }}
        </span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Assigned Lawyer</th>
                            <th>Membership No.</th>
                            <th>Members Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assistants as $assistant)
                            <tr>
                                <td>
                                    {{ $assistants->firstItem() + $loop->index }}
                                </td>
                                <td>
                                    <strong>{{ $assistant->full_name }}</strong>
                                </td>
                                <td>{{ $assistant->email ?? '—' }}</td>
                                <td>{{ $assistant->mobile ?? '—' }}</td>
                                <td>
                                    {{ $assistant->lawyer?->full_name ?? 'Not assigned' }}
                                </td>
                                <td>{{ $assistant->membership_number ?? '—' }}</td>
                                <td>
                                    @if ($assistant->member_status === 'lawyer_assistant')
                                        <span class="badge bg-primary">Lawyer Assistant</span>
                                    @elseif ($assistant->member_status === 'apprentice')
                                        <span class="badge bg-info text-dark">Apprentice</span>
                                    @endif
                                </td>
                                <td>
                                    <a
                                        href="{{ route('assistants.show', $assistant) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No assistants assigned yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $assistants->links() }}
        </div>
    </div>
</div>
@endsection
