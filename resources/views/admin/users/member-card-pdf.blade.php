<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; margin: 0; padding: 0; }
    .head { background: #1e3a8a; color: #fff; text-align: center; padding: 10px 0; }
    .head h1 { margin: 0; font-size: 14pt; }
    .head p { margin: 2px 0 0; font-size: 8pt; }
    .photo { text-align: center; padding: 12px 0 4px; }
    .photo img { width: 80px; height: 80px; border: 2px solid #1e3a8a; }
    .name { text-align: center; font-size: 13pt; font-weight: bold; margin: 4px 8px 0; }
    .mno { text-align: center; font-size: 10pt; font-weight: bold; color: #1e3a8a; margin-bottom: 8px; }
    table.info { width: 100%; border-collapse: collapse; font-size: 9pt; }
    table.info td { padding: 5px 14px; border-top: 1px solid #e5e7eb; vertical-align: top; }
    table.info td.l { color: #64748b; width: 38%; }
    table.info td.v { font-weight: bold; }
    .qr { text-align: center; padding-top: 8px; }
    .foot { text-align: center; font-size: 7pt; color: #94a3b8; padding-top: 6px; }
</style>
</head>
<body>
    <div class="head">
        <h1>{{ config('app.name') }}</h1>
        <p>Member Identity Card</p>
    </div>

    <div class="photo">
        @if($photoUrl)
            <img src="{{ $photoUrl }}">
        @endif
    </div>

    <div class="name">{{ $member->full_name }}</div>
    <div class="mno">{{ $member->membership_number ?: '' }}</div>

    <table class="info">
        <tr><td class="l">Father's Name</td><td class="v">{{ $member->father_name ?: '—' }}</td></tr>
        <tr><td class="l">Phone</td><td class="v">{{ $member->mobile }}</td></tr>
        <tr><td class="l">Email</td><td class="v">{{ $member->email ?: '—' }}</td></tr>
        <tr><td class="l">Date of Birth</td><td class="v">{{ $member->dob ? \Carbon\Carbon::parse($member->dob)->format('d-m-Y') : '—' }}</td></tr>
        <tr><td class="l">Blood Group</td><td class="v">{{ $member->blood_group ?: '—' }}</td></tr>
    </table>

    {{-- <div class="qr"><img src="{{ $qrDataUri }}" width="70" height="70"></div>
    <div class="foot">Scan to verify</div> --}}
</body>
</html>