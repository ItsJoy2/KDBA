@extends('layouts.app')

@section('title', 'My Sticker Order')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0">My Sticker Order</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $order->order_number }}</h5>
                <a href="{{ route('member.vakalatnama.index') }}" class="btn btn-secondary btn-sm">
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <strong>Quantity:</strong> {{ $order->quantity }}
                    </div>
                    <div class="col-md-4">
                        <strong>Total:</strong> ৳{{ number_format((float) $order->total_amount, 2) }}
                    </div>
                    <div class="col-md-4">
                        <strong>Payment:</strong> {{ ucfirst($order->payment_status) }}
                    </div>
                </div>

                @if ($order->payment_status !== 'paid')
                    <div class="alert alert-warning">
                        Your stickers will be issued after payment approval.
                    </div>
                @else
                    <div class="alert alert-success">
                        Your payment has been approved. Please contact the Bar Association office for printing.
                    </div>
                @endif

                <h5>Sticker Records</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Record No.</th>
                                <th>Status</th>
                                <th>Print Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->stickers as $sticker)
                                <tr>
                                    <td>{{ $sticker->record_number }}</td>
                                    <td>{{ ucfirst($sticker->status) }}</td>
                                    <td>{{ $sticker->print_count > 0 ? 'Printed' : 'Not printed' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection