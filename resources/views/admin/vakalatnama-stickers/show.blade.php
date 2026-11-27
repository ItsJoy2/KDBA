@extends('layouts.app')

@section('title', 'Sticker Order Details')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">Sticker Order Details</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $order->order_number }}</h5>
                <a href="{{ route('admin.vakalatnama.index') }}" class="btn btn-secondary btn-sm">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <strong>Member:</strong> {{ $order->user?->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Membership No.:</strong> {{ $order->user?->membership_number ?? 'N/A' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Quantity:</strong> {{ $order->quantity }}
                    </div>
                    <div class="col-md-4">
                        <strong>Unit Price:</strong> ৳{{ number_format((float) $order->unit_price, 2) }}
                    </div>
                    <div class="col-md-4">
                        <strong>Total:</strong> ৳{{ number_format((float) $order->total_amount, 2) }}
                    </div>
                    <div class="col-md-6">
                        <strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}
                    </div>
                    <div class="col-md-6">
                        <strong>Order Status:</strong> {{ ucfirst($order->status) }}
                    </div>
                </div>

                @if ($order->payment_status !== 'paid')
                    <div class="alert alert-warning">
                        Stickers cannot be printed until payment is approved.
                    </div>
                @endif

                <h5 class="mb-3">Sticker Records</h5>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Record No.</th>
                                <th>Status</th>
                                <th>Print Count</th>
                                <th>Last Printed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->stickers as $sticker)
                                <tr>
                                    <td>{{ $sticker->record_number }}</td>
                                    <td>{{ ucfirst($sticker->status) }}</td>
                                    <td>{{ $sticker->print_count }}</td>
                                    <td>{{ $sticker->printed_at?->format('d M Y, h:i A') ?? 'Not printed' }}</td>
                                    <td class="d-flex flex-wrap gap-1">
                                        @if ($order->payment_status === 'paid')
                                            @if ($sticker->print_count === 0)
                                                @can('vakalatnama.print')
                                                    <form method="POST" action="{{ route('admin.vakalatnama.print', $sticker) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            Print
                                                        </button>
                                                    </form>
                                                @endcan
                                            @else
                                                @can('vakalatnama.reprint')
                                                    <form method="POST" action="{{ route('admin.vakalatnama.reprint', $sticker) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                                            Reprint
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No sticker records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($order->transaction)
                    <hr>
                    <h5>Payment Information</h5>
                    <p class="mb-1"><strong>Transaction Reference:</strong> {{ $order->transaction->trx_id ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Amount:</strong> ৳{{ number_format((float) $order->transaction->amount, 2) }}</p>
                    <p class="mb-0"><strong>Status:</strong> {{ ucfirst($order->transaction->status) }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection