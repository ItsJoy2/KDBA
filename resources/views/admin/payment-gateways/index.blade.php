@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">Payment Gateways</h4>
            <p class="text-muted mb-0">Manage payment methods used for membership payments.</p>
        </div>

        @if(auth()->user()->hasPermission('gateways.manage'))
            <button type="button" class="btn btn-primary"
                    data-bs-toggle="modal" data-bs-target="#gatewayCreateModal">
                <i class="bi bi-plus-circle me-1"></i> Add Gateway
            </button>
        @endif
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Gateway List</h5>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Gateway</th>
                        <th>Type</th>
                        <th>Account Number</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        @if(auth()->user()->hasPermission('gateways.manage'))
                            <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($gateways as $gateway)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong>{{ $gateway->name }}</strong>
                                @if($gateway->instruction)
                                    <div class="small text-muted">
                                        {{ \Illuminate\Support\Str::limit($gateway->instruction, 55) }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-secondary">
                                    {{ strtoupper($gateway->type) }}
                                </span>
                            </td>

                            <td>{{ $gateway->account_number }}</td>

                            <td>{{ $gateway->sort_order }}</td>

                            <td>
                                @if($gateway->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>

                            @if(auth()->user()->hasPermission('gateways.manage'))
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#gatewayEditModal{{ $gateway->id }}"
                                            title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#gatewayDeleteModal{{ $gateway->id }}"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-credit-card fs-3 d-block mb-2"></i>
                                No payment gateways found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

            <div class="card-footer">
                @include('layouts.partials.__pagination', ['items' => $gateways])
            </div>
    </div>
</div>

{{-- Create Gateway Modal --}}
@if(auth()->user()->hasPermission('gateways.manage'))
<div class="modal fade" id="gatewayCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.payment-gateways.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Payment Gateway</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @include('admin.payment-gateways.partials.form-fields', [
                        'gateway' => null,
                        'prefix' => 'create'
                    ])
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Gateway
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit and Delete Modals --}}
@foreach($gateways as $gateway)

    {{-- Edit Modal --}}
    <div class="modal fade" id="gatewayEditModal{{ $gateway->id }}"
         tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.payment-gateways.update', $gateway) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payment Gateway</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        @include('admin.payment-gateways.partials.form-fields', [
                            'gateway' => $gateway,
                            'prefix' => 'edit-'.$gateway->id
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Update Gateway
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="gatewayDeleteModal{{ $gateway->id }}"
         tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.payment-gateways.destroy', $gateway) }}"
                      method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title">Delete Payment Gateway</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="text-center py-3">
                            <i class="bi bi-exclamation-triangle text-danger fs-1"></i>
                            <h5 class="mt-3">Are you sure?</h5>
                            <p class="text-muted mb-0">
                                Delete gateway
                                <strong>{{ $gateway->name }}</strong>?
                            </p>
                            <small class="text-danger">
                                Existing transactions must retain their gateway history.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Delete Gateway
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endforeach
@endif

@endsection
