
@extends('layouts.app')

@section('title', 'Payment Gateways')

@section('content')
<div class="container-fluid">
    @include('components.global-feedback')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Payment Gateways</h3>

            <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#gatewayModal"
                    onclick="resetGatewayForm()">
                <i class="bi bi-plus-circle"></i> Add Gateway
            </button>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Account Number</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($gateways as $gateway)
                        <tr>
                            <td>{{ $gateways->firstItem() + $loop->index }}</td>
                            <td>{{ $gateway->name }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $gateway->type)) }}</td>
                            <td>{{ $gateway->account_number ?: '—' }}</td>
                            <td>
                                @if($gateway->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-info"
                                    onclick="editGateway({{ Illuminate\Support\Js::from([
                                        'id' => $gateway->id,
                                        'name' => $gateway->name,
                                        'type' => $gateway->type,
                                        'account_number' => $gateway->account_number,
                                        'instruction' => $gateway->instruction,
                                        'is_active' => $gateway->is_active,
                                        'sort_order' => $gateway->sort_order,
                                    ]) }})">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <form action="{{ route('admin.payment-gateways.destroy', $gateway) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this payment gateway?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No payment gateways found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $gateways->links('admin.layouts.partials.pagination-links') }}
        </div>
    </div>
</div>

{{-- Gateway Create/Edit Modal --}}
<div class="modal fade" id="gatewayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="gatewayForm"
              action="{{ route('admin.payment-gateways.store') }}">
            @csrf

            <div id="gatewayMethod"></div>

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gatewayModalTitle">Add Payment Gateway</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Gateway Name</label>
                        <input type="text" name="name" id="gatewayName"
                               class="form-control" maxlength="100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gateway Type</label>
                        <select name="type" id="gatewayType" class="form-select" required>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="bank">Bank</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number"
                               id="gatewayAccount" class="form-control"
                               maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Instructions</label>
                        <textarea name="instruction" id="gatewayInstruction"
                                  class="form-control" rows="4"
                                  maxlength="5000"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" id="gatewaySort"
                               class="form-control" min="0" max="65535" value="0">
                    </div>

                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="gatewayActive"
                               value="1" class="form-check-input" checked>
                        <label class="form-check-label" for="gatewayActive">
                            Active
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn btn-primary">
                        Save Gateway
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const gatewayStoreUrl = @json(route('admin.payment-gateways.store'));
    const gatewayUpdateUrl = @json(route('admin.payment-gateways.update', ['gateway' => '__ID__']));

    function resetGatewayForm() {
        const form = document.getElementById('gatewayForm');

        form.action = gatewayStoreUrl;
        document.getElementById('gatewayMethod').innerHTML = '';
        document.getElementById('gatewayModalTitle').textContent = 'Add Payment Gateway';

        form.reset();
        document.getElementById('gatewayActive').checked = true;
        document.getElementById('gatewaySort').value = 0;
    }

    function editGateway(gateway) {
        const form = document.getElementById('gatewayForm');

        form.action = gatewayUpdateUrl.replace('__ID__', gateway.id);

        document.getElementById('gatewayMethod').innerHTML =
            '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('gatewayModalTitle').textContent = 'Edit Payment Gateway';

        document.getElementById('gatewayName').value = gateway.name ?? '';
        document.getElementById('gatewayType').value = gateway.type;
        document.getElementById('gatewayAccount').value = gateway.account_number ?? '';
        document.getElementById('gatewayInstruction').value = gateway.instruction ?? '';
        document.getElementById('gatewaySort').value = gateway.sort_order ?? 0;
        document.getElementById('gatewayActive').checked = Boolean(gateway.is_active);

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('gatewayModal')
        ).show();
    }
</script>
@endpush
