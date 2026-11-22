<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; margin: 0; padding: 0; background-color: #fbf8f1; color: #1c2433; }

    .head { background-color: #0b1f3a; text-align: center; padding: 14px 0 12px; border-bottom: 3px solid #c9a24b; }
    .head h1 { margin: 0; font-size: 13pt; color: #e8d08a; letter-spacing: 1px; }
    .head p { margin: 4px 0 0; font-size: 7pt; color: #cfd8e6; letter-spacing: 2px; }

    .photo { text-align: center; padding: 14px 0 4px; }
    .photo img { width: 85px; height: 85px; border: 3px solid #c9a24b; }

    .name { text-align: center; font-size: 14pt; font-weight: bold; color: #0b1f3a; margin: 6px 8px 4px; font-family: serif; }

    .mno-wrap { text-align: center; margin-bottom: 10px; }
    .mno { display: inline-block; background-color: #e8d08a; color: #0b1f3a; font-size: 9.5pt; font-weight: bold;
           padding: 3px 14px; border: 1px solid #c9a24b; }

    table.info { width: 100%; border-collapse: collapse; font-size: 9pt; }
    table.info td { padding: 6px 16px; border-top: 1px solid #eee6d3; vertical-align: top; }
    table.info td.l { color: #7a8497; width: 38%; font-size: 7.5pt; text-transform: uppercase; }
    table.info td.v { font-weight: bold; color: #1c2433; }

    .foot { background-color: #0b1f3a; color: #e8d08a; text-align: center; font-size: 7pt;
            padding: 7px 0; border-top: 3px solid #c9a24b; }
</style>
</head>
<body>
    <div class="head">
        <h1>{{ strtoupper(config('app.name')) }}</h1>
        <p>MEMBER IDENTITY CARD</p>
    </div>

    <div class="photo">
        @if($photoUrl)
            <img src="{{ $photoUrl }}">
        @endif
    </div>

    <div class="name">{{ $member->full_name }}</div>
    <div class="mno-wrap">
        <span class="mno">{{ $member->membership_number ?: '—' }}</span>
    </div>

    <table class="info">
        <tr><td class="l">Father's Name</td><td class="v">{{ $member->father_name ?: '—' }}</td></tr>
        <tr><td class="l">Phone</td><td class="v">{{ $member->mobile }}</td></tr>
        <tr><td class="l">Email</td><td class="v">{{ $member->email ?: '—' }}</td></tr>
        <tr><td class="l">Date of Birth</td><td class="v">{{ $member->dob ? \Carbon\Carbon::parse($member->dob)->format('d-m-Y') : '—' }}</td></tr>
        <tr><td class="l">Blood Group</td><td class="v">{{ $member->blood_group ?: '—' }}</td></tr>
    </table>

    {{-- <div class="qr"><img src="{{ $qrDataUri }}" width="70" height="70"></div> --}}

    <div class="foot">Verified at {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>