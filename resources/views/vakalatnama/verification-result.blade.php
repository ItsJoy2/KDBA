<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sticker Verification Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Vakalatnama Sticker Verification</h5>
                    </div>

                    <div class="card-body">
                        @if (!empty($result['valid']))
                            <div class="alert alert-success">
                                <strong>Valid Sticker</strong>
                                <div>This sticker record was found in the association system.</div>
                            </div>

                            <table class="table table-bordered">
                                <tr>
                                    <th>Record Number</th>
                                    <td>{{ $result['record_number'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Member Name</th>
                                    <td>{{ $result['member_name'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Membership Number</th>
                                    <td>{{ $result['membership_number'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Sticker Price</th>
                                    <td>৳{{ number_format((float) ($result['unit_price'] ?? 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Sticker Status</th>
                                    <td>{{ ucfirst($result['status'] ?? 'unknown') }}</td>
                                </tr>
                                <tr>
                                    <th>Printed</th>
                                    <td>{{ !empty($result['is_printed']) ? 'Yes' : 'No' }}</td>
                                </tr>
                            </table>
                        @else
                            <div class="alert alert-danger mb-0">
                                <strong>Invalid Sticker</strong>
                                <div>No valid sticker record was found for this verification number.</div>
                            </div>
                        @endif

                        <a href="{{ route('vakalatnama.verify') }}" class="btn btn-outline-primary mt-3">
                            Verify Another Sticker
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>