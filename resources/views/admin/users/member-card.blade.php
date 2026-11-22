<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $member->full_name }} - Member ID</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;flex-direction:column;align-items:center;
             justify-content:center;background:#eef2f7;
             font-family:system-ui,Arial,sans-serif;padding:16px}

        .card{width:100%;max-width:380px;background:#fff;border-radius:16px;overflow:hidden;
              box-shadow:0 10px 30px rgba(0,0,0,.12)}
        .head{background:#1e3a8a;color:#fff;text-align:center;padding:16px}
        .head h1{margin:0;font-size:18px;letter-spacing:.5px}
        .head p{margin:4px 0 0;font-size:12px;opacity:.85}
        .photo{display:flex;justify-content:center;margin-top:-1px;padding:20px 0 8px}
        .photo img,.photo .ph{width:130px;height:130px;border-radius:50%;object-fit:cover;
              border:4px solid #1e3a8a;background:#e5e7eb}
        .name{text-align:center;font-size:20px;font-weight:700;margin:6px 16px 2px}
        .badge{display:block;text-align:center;font-size:14px;font-weight:700;color:#1e3a8a;
               margin-bottom:12px}
        table{width:100%;border-collapse:collapse;font-size:14px}
        td{padding:10px 20px;border-top:1px solid #f1f5f9;vertical-align:top}
        td:first-child{color:#64748b;width:40%}
        td:last-child{font-weight:600;word-break:break-word}
        .foot{background:#f8fafc;text-align:center;padding:10px;font-size:11px;color:#94a3b8}

        /* Download buttons */
        .actions{display:flex;gap:10px;margin-top:16px;width:100%;max-width:380px}
        .btn{flex:1;padding:11px;border:0;border-radius:10px;font-size:14px;font-weight:600;
             text-align:center;text-decoration:none;cursor:pointer;color:#fff}
        .btn.jpg{background:#1e3a8a}
        .btn.pdf{background:#0f766e}
        .btn:disabled{opacity:.6;cursor:wait}
    </style>
</head>
<body>

<div class="card" id="idcard">
    <div class="head">
        <h1>{{ config('app.name') }}</h1>
        <p>Member Identity Card</p>
    </div>

    <div class="photo">
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $member->full_name }}">
        @else
            <div class="ph"></div>
        @endif
    </div>

    <div class="name">{{ $member->full_name }}</div>
    <span class="badge">{{ $member->membership_number ?: '—' }}</span>

    <table>
        <tr><td>Father's Name</td><td>{{ $member->father_name ?: '—' }}</td></tr>
        <tr><td>Phone</td><td>{{ $member->mobile }}</td></tr>
        <tr><td>Email</td><td>{{ $member->email ?: '—' }}</td></tr>
        <tr><td>Date of Birth</td><td>{{ $member->dob ? \Carbon\Carbon::parse($member->dob)->format('d-m-Y') : '—' }}</td></tr>
        <tr><td>Blood Group</td><td>{{ $member->blood_group ?: '—' }}</td></tr>
    </table>

    <div class="foot">Verified at {{ now()->format('d M Y, h:i A') }}</div>
</div>

{{-- Download buttons (card-er baire, tai JPG-te ashbe na) --}}
    @auth
        <div class="actions">
            <button type="button" class="btn jpg" id="btn-jpg" onclick="downloadJpg()">Download JPG</button>
            <a class="btn pdf" href="{{ route('member.card.pdf', $member->ppt) }}">Download PDF</a>
        </div>
    @endauth

@auth
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    async function downloadJpg() {
        const btn = document.getElementById('btn-jpg');
        btn.disabled = true;
        btn.textContent = 'Preparing...';

        try {
            const canvas = await html2canvas(document.getElementById('idcard'), {
                scale: 3,
                backgroundColor: '#ffffff',
                useCORS: true
            });

            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/jpeg', 0.95);
            a.download = 'member-card-{{ $member->membership_number ?: $member->id }}.jpg';
            document.body.appendChild(a);
            a.click();
            a.remove();
        } catch (e) {
            alert('Unable to create JPG. Please try again.');
            console.error(e);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Download JPG';
        }
    }

    if (new URLSearchParams(location.search).get('download') === 'jpg') {
        window.addEventListener('load', downloadJpg);
    }
</script>
@endauth

</body>
</html>