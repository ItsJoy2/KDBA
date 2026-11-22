<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $member->full_name }} - Member ID</title>
    <style>
        :root{
            --navy:#0b1f3a;
            --navy-2:#14305a;
            --gold:#c9a24b;
            --gold-light:#e8d08a;
            --ivory:#fbf8f1;
            --ink:#1c2433;
            --muted:#7a8497;
            --line:#eee6d3;
        }
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;flex-direction:column;align-items:center;
             justify-content:center;padding:16px;
             background:linear-gradient(160deg,#e9edf4 0%,#f6f1e4 100%);
             font-family:system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:var(--ink)}

        .card{width:100%;max-width:380px;background:var(--ivory);border-radius:18px;overflow:hidden;
              border:1px solid var(--gold);
              box-shadow:0 18px 40px rgba(11,31,58,.22)}

        .head{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-2) 100%);
              color:#fff;text-align:center;padding:22px 16px 62px;
              border-bottom:3px solid var(--gold)}
        .head h1{margin:0;font-size:18px;font-weight:700;letter-spacing:1px;color:var(--gold-light);
                 text-transform:uppercase}
        .head p{margin:6px 0 0;font-size:11px;letter-spacing:3px;text-transform:uppercase;
                color:#cfd8e6}

        .photo{display:flex;justify-content:center;margin-top:-52px;position:relative;z-index:2}
        .photo img,.photo .ph{width:120px;height:120px;border-radius:50%;object-fit:cover;
              border:4px solid var(--gold);background:#e5e7eb;
              box-shadow:0 0 0 4px #fff,0 8px 18px rgba(11,31,58,.25)}

        .name{text-align:center;font-size:21px;font-weight:700;margin:16px 16px 4px;
              color:var(--navy);font-family:Georgia,"Times New Roman",serif}
        .badge{display:block;width:max-content;margin:0 auto 14px;padding:5px 16px;
               font-size:13px;font-weight:700;letter-spacing:1px;color:var(--navy);
               background:linear-gradient(135deg,var(--gold-light),var(--gold));
               border-radius:999px;text-align:center}

        table{width:100%;border-collapse:collapse;font-size:14px}
        td{padding:11px 22px;border-top:1px solid var(--line);vertical-align:top}
        td:first-child{color:var(--muted);width:40%;font-size:12px;text-transform:uppercase;
                       letter-spacing:.6px;padding-top:13px}
        td:last-child{font-weight:600;color:var(--ink);word-break:break-word}

        .foot{background:var(--navy);text-align:center;padding:11px;font-size:11px;
              letter-spacing:.5px;color:var(--gold-light);border-top:3px solid var(--gold)}

        /* Download buttons */
        .actions{display:flex;gap:10px;margin-top:18px;width:100%;max-width:380px}
        .btn{flex:1;padding:12px;border:0;border-radius:10px;font-size:14px;font-weight:600;
             text-align:center;text-decoration:none;cursor:pointer}
        .btn.jpg{background:linear-gradient(135deg,var(--gold-light),var(--gold));color:var(--navy)}
        .btn.pdf{background:linear-gradient(135deg,var(--navy-2),var(--navy));color:var(--gold-light)}
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