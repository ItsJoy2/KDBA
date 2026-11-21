
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Member QR Code - {{ $user->full_name }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .qr-card {
            background: #fff;
            max-width: 480px;
            width: 100%;
            padding: 35px;
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 10px 35px rgba(0,0,0,.08);
        }

        .qr-image {
            width: 280px;
            max-width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        @media print {
            body { background: #fff; }
            .qr-card { box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="qr-card">
    <h4 class="fw-bold mb-1">Khagrachari District Bar Association</h4>
    <p class="text-muted mb-4">Member Digital Identity</p>

    <h5 class="fw-bold">{{ $user->full_name }}</h5>

    <p class="text-muted mb-4">
        {{ $user->membership_number ?? 'Member' }}
    </p>

    <img src="{{ $qrCode }}" class="qr-image" alt="Member QR Code">

    <p class="small text-muted mt-3">
        Scan this QR code to view member profile.
    </p>

    <div class="no-print mt-4">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print QR Code
        </button>

        <a href="{{ $profileUrl }}" target="_blank"
           class="btn btn-outline-secondary">
            View Profile
        </a>
    </div>
</div>

</body>
</html>
