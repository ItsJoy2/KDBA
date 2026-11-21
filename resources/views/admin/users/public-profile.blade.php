
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">

    <title>Member Profile - {{ $user->full_name }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #102a43, #1e6091);
            padding: 35px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-card {
            max-width: 540px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0,0,0,.2);
        }

        .profile-header {
            background: linear-gradient(135deg, #12344d, #176b87);
            color: #fff;
            text-align: center;
            padding: 35px 20px 65px;
        }

        .profile-photo {
            width: 125px;
            height: 125px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fff;
            margin-top: -65px;
            background: #e9ecef;
        }

        .profile-info {
            padding: 25px 30px 30px;
        }

        .info-item {
            padding: 14px 0;
            border-bottom: 1px solid #edf0f2;
        }

        .info-item:last-child {
            border-bottom: 0;
        }

        .info-label {
            color: #6c757d;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #243746;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
    </style>
</head>
<body>

<div class="profile-card">

    <div class="profile-header">
        <h4 class="fw-bold mb-2">KHAGRACHARI DISTRICT BAR ASSOCIATION</h4>
        <p class="mb-0 opacity-75">Digital Member Profile</p>
    </div>

    <div class="text-center">
        @if($photoUrl)
            <img src="{{ $photoUrl }}" class="profile-photo" alt="Member Photo">
        @else
            <div class="profile-photo d-inline-flex align-items-center justify-content-center fw-bold fs-1">
                {{ mb_substr($user->full_name ?? 'M', 0, 1) }}
            </div>
        @endif

        <h4 class="fw-bold mt-3 mb-1">{{ $user->full_name }}</h4>

        @if($user->membership_number)
            <p class="text-muted mb-0">
                Membership No: {{ $user->membership_number }}
            </p>
        @endif
    </div>

    <div class="profile-info">

        <div class="info-item">
            <div class="info-label">Father's Name</div>
            <div class="info-value">
                {{ $user->father_name ?? 'N/A' }}
            </div>
        </div>

        <div class="info-item">
            <div class="info-label">Email Address</div>
            <div class="info-value">
                {{ $user->email ?: 'N/A' }}
            </div>
        </div>

        <div class="info-item">
            <div class="info-label">Mobile Number</div>
            <div class="info-value">
                {{ $user->mobile ?: 'N/A' }}
            </div>
        </div>

        <div class="info-item">
            <div class="info-label">Membership Number</div>
            <div class="info-value">
                {{ $user->membership_number ?: 'N/A' }}
            </div>
        </div>

    </div>

    <div class="text-center text-muted small pb-4">
        Official Digital Member Identity
    </div>

</div>

</body>
</html>
