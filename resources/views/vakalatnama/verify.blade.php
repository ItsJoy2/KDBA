<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vakalatnama Sticker Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header text-center">
                        <h5 class="mb-0">Khagrachari District Bar Association</h5>
                        <small>Vakalatnama Sticker Verification</small>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('vakalatnama.verify.lookup') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="unique_number" class="form-label">
                                    Sticker Verification Number
                                </label>
                                <input
                                    type="text"
                                    name="unique_number"
                                    id="unique_number"
                                    class="form-control"
                                    inputmode="numeric"
                                    minlength="18"
                                    maxlength="18"
                                    pattern="[0-9]{18}"
                                    placeholder="Enter 18-digit number"
                                    value="{{ old('unique_number') }}"
                                    required
                                >
                                @error('unique_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Verify Sticker
                            </button>
                        </form>
                    </div>

                    <div class="card-footer text-center text-muted small">
                        Verify the authenticity of a Vakalatnama sticker.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>