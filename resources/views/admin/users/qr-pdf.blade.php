<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; text-align: center; }
    .name { font-size: 12pt; font-weight: bold; margin-top: 6px; }
    .sub  { font-size: 9pt; color: #555; margin-top: 2px; }
</style>
</head>
<body>
    <barcode code="{{ $url }}" type="QR" class="barcode" size="1.8" error="M" disableborder="1" />
    <div class="name">{{ $member->full_name }}</div>
    <div class="sub">{{ $member->membership_number ?: '' }}</div>
</body>
</html>