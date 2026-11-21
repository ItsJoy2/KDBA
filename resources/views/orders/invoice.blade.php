
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">

    <title>Order Invoice - {{ $order->order_number }}</title>

    <style>
        * {
            font-family: solaimanlipi;
            box-sizing: border-box;
        }

        body {
            font-family: solaimanlipi;
            font-size: 11px;
            color: #263238;
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6,
        strong, b,
        .header h2,
        .invoice-heading h3,
        .section-title,
        .info-value,
        .grand-total {
            font-family: solaimanlipi;
            font-weight: bold;
        }

        .taka-symbol {
            font-family: solaimanlipi;
            font-weight: normal;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h2 {
            font-size: 19px;
            color: #1e3a5f;
            margin: 0;
        }

        .header p {
            margin: 2px 0;
            color: #64748b;
        }

        .invoice-heading {
            text-align: center;
            margin-bottom: 15px;
        }

        .invoice-heading h3 {
            font-size: 17px;
            margin: 0;
            color: #1e3a5f;
        }

        .invoice-info {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .invoice-info td {
            width: 50%;
            vertical-align: top;
            padding: 3px 0;
        }

        .invoice-info .info-left {
            text-align: left;
            padding-right: 10px;
        }

        .invoice-info .info-right {
            text-align: right;
            padding-left: 10px;
        }

        .info-label {
            color: #64748b;
            display: inline-block;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 7px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .items-table th {
            background-color: #1e3a5f;
            color: #ffffff;
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
        }

        .items-table td {
            border: 1px solid #dce3eb;
            padding: 7px 5px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .description {
            color: #64748b;
            font-size: 9px;
        }

        .serial-list {
            color: #64748b;
            font-size: 9px;
            line-height: 1.5;
            margin-top: 3px;
        }

        .serial-label {
            font-weight: bold;
        }

        .total-wrapper {
            width: 100%;
            margin-top: 12px;
        }

        .total-table {
            width: 55%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .total-table td {
            padding: 6px;
            border-bottom: 1px solid #dce3eb;
        }

        .grand-total td {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a5f;
            border-top: 2px solid #1e3a5f;
        }

        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-other {
            background: #e2e8f0;
            color: #334155;
        }

        .footer {
            margin-top: 35px;
            width: 100%;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            width: 50%;
            vertical-align: top;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
        }

        .signature {
            display: inline-block;
            text-align: center;
            min-width: 150px;
            padding-top: 35px;
            border-top: 1px solid #333;
        }

        .footer-note {
            text-align: center;
            color: #64748b;
            font-size: 9px;
            border-top: 1px solid #dce3eb;
            padding-top: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    {{-- Association Header --}}
    <div class="header">
        <h2>Khagrachari District Bar Association</h2>

        <p>
            Khagrachari, Bangladesh
        </p>
    </div>

    {{-- Invoice Heading --}}
    <div class="invoice-heading">
        <h3>ORDER INVOICE</h3>
    </div>

    {{-- Order Information --}}
    <table class="invoice-info">
        <tr>
            <td class="info-left">
                <span class="info-label">Order Number:</span>
                <strong>{{ $order->order_number }}</strong>
            </td>

            <td class="info-right">
                <span class="info-label">Order Date:</span>
                {{ $order->created_at?->format('d M Y') ?? 'N/A' }}
            </td>
        </tr>

        <tr>
            <td class="info-left">
                <span class="info-label">Member Name:</span>
                <strong>{{ $order->user?->full_name ?? 'N/A' }}</strong>
            </td>

            <td class="info-right">
                <span class="info-label">Membership No:</span>
                {{ $order->user?->membership_number ?? 'N/A' }}
            </td>
        </tr>

        <tr>
            <td class="info-left">
                <span class="info-label">Mobile:</span>
                {{ $order->user?->mobile ?? 'N/A' }}
            </td>

            <td class="info-right">
                <span class="info-label">Purchased By:</span>
                {{ $order->purchaser?->full_name ?? 'N/A' }}
            </td>
        </tr>

        <tr>
            <td class="info-left">
                <span class="info-label">Order Status:</span>

                @php
                    $orderStatusClass = match (strtolower($order->order_status)) {
                        'completed' => 'status-paid',
                        'pending', 'processing' => 'status-pending',
                        default => 'status-other',
                    };
                @endphp

                <span class="status {{ $orderStatusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                </span>
            </td>

            <td class="info-right">
                <span class="info-label">Payment Status:</span>

                @php
                    $paymentStatusClass = match (strtolower($order->payment_status)) {
                        'paid' => 'status-paid',
                        'pending', 'processing' => 'status-pending',
                        default => 'status-other',
                    };
                @endphp

                <span class="status {{ $paymentStatusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- Order Items --}}
    <div class="section-title">
        Order Details
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="7%" class="text-center">SL</th>
                <th width="43%">Description</th>
                <th width="15%" class="text-right">Unit Price</th>
                <th width="12%" class="text-center">Qty</th>
                <th width="23%" class="text-right">Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($order->items as $item)
                @php
                    $itemSerials = $order->serials
                        ->where('product_id', $item->product_id)
                        ->pluck('serial_number')
                        ->values();
                @endphp

                <tr>
                    <td class="text-center">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        <strong>
                            {{ $item->product?->name ?? 'Product Unavailable' }}
                        </strong>

                        {{-- Product Serial Numbers --}}
                        @if($itemSerials->isNotEmpty())
                            <div class="serial-list">
                                <span class="serial-label">Serial:</span>

                                {{ $itemSerials->implode(', ') }}
                            </div>
                        @endif
                    </td>

                    <td class="text-right">
                        {{ number_format((float) $item->price, 2) }}
                    </td>

                    <td class="text-center">
                        {{ $item->quantity }}
                    </td>

                    <td class="text-right">
                        {{ number_format(
                            (float) $item->price * (int) $item->quantity,
                            2
                        ) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        No order items found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Grand Total --}}
    <div class="total-wrapper">
        <table class="total-table">
            <tr class="grand-total">
                <td>
                    Grand Total
                </td>

                <td class="text-right">
                    <span class="taka-symbol">৳</span>
                    {{ number_format((float) $order->total_amount, 2) }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left" width="50%">
                    <strong>Note:</strong><br>
                    This is a system-generated order invoice.
                </td>

                <td class="footer-right" width="50%">
                    <div class="signature">
                        Authorized Signature
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer Note --}}
    <div class="footer-note">
        Thank you. Please preserve this invoice for future reference.
        <br>
        This invoice was generated electronically.
    </div>

</body>
</html>
