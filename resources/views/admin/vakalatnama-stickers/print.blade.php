<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vakalatnama Sticker - {{ $sticker->record_number }}</title>

    <style>
        @page {
            size: 70mm 45mm;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #000;
        }

        .sticker {
            width: 70mm;
            min-height: 45mm;
            padding: 2.5mm;
            overflow: hidden;
            page-break-after: always;
        }

        .header {
            text-align: center;
            font-size: 10pt;
            font-weight: 700;
            border-bottom: 1px solid #000;
            padding-bottom: 1.5mm;
            margin-bottom: 2mm;
        }

        .content {
            display: flex;
            gap: 2mm;
            align-items: flex-start;
        }

        .member-photo {
            width: 14mm;
            height: 17mm;
            object-fit: cover;
            border: 1px solid #000;
        }

        .details {
            flex: 1;
            font-size: 8pt;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .details strong {
            font-weight: 700;
        }

        .barcode-area {
            margin-top: 1.5mm;
            text-align: center;
        }

        #barcode {
            width: 100%;
            height: 10mm;
        }

        .unique-number {
            font-family: monospace;
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: 1px;
            text-align: center;
            margin-top: 0.5mm;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #000;
            margin-top: 1mm;
            padding-top: 1mm;
            font-size: 6.5pt;
        }

        .print-actions {
            padding: 10px;
            text-align: center;
        }

        @media print {
            .print-actions {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Print Sticker</button>
        <button type="button" onclick="window.close()">Close</button>
    </div>

    <main class="sticker">
        <div class="header">
            KAGRACHARI DISTRICT BAR ASSOCIATION
            <div style="font-size: 8pt; font-weight: 400;">Vakalatnama Sticker</div>
        </div>

        <div class="content">
            <img
                class="member-photo"
                src="{{ $memberPhotoUrl ?? asset('images/default-avatar.png') }}"
                alt="Member photo"
                onerror="this.style.display='none'"
            >

            <div class="details">
                <div><strong>Member:</strong> {{ $sticker->user?->name ?? 'N/A' }}</div>
                <div><strong>Phone:</strong> {{ $sticker->user?->phone ?? 'N/A' }}</div>
                <div><strong>Record:</strong> {{ $sticker->record_number }}</div>
                <div><strong>Price:</strong> ৳{{ number_format((float) ($sticker->order?->unit_price ?? 0), 2) }}</div>
                <div><strong>Issued:</strong> {{ $sticker->issued_at?->format('d-m-Y h:i A') ?? now()->format('d-m-Y h:i A') }}</div>
            </div>
        </div>

        <div class="barcode-area">
            <svg id="barcode"></svg>
            <div class="unique-number">{{ $sticker->unique_number }}</div>
        </div>

        <div class="footer">
            <span>Verify authenticity using barcode</span>
            <span>{{ now()->format('d-m-Y h:i A') }}</span>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            JsBarcode('#barcode', @json($sticker->unique_number), {
                format: 'CODE128',
                displayValue: false,
                margin: 0,
                height: 35,
                width: 1.5
            });

            window.addEventListener('afterprint', function () {
                // The server-side print log is already recorded.
            });

            window.print();
        });
    </script>
</body>
</html>