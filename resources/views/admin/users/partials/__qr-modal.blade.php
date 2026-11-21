
{{-- QR Modal --}}
<div class="modal fade" id="memberQrModal" tabindex="-1"
     aria-labelledby="memberQrModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="memberQrModalLabel">
                        Member QR Code
                    </h5>
                    <small class="text-muted" id="qrMemberName"></small>
                </div>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

                <div id="qrLoading" class="py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="text-muted mt-2">Loading QR Code...</p>
                </div>

                <div id="qrContent" class="d-none">
                    <div class="border rounded-3 p-3 d-inline-block">
                        <img id="memberQrImage"
                             src=""
                             alt="Member QR Code"
                             class="img-fluid"
                             style="width:280px;height:280px;">
                    </div>

                    <p class="text-muted small mt-3">
                        Scan to view member profile.
                    </p>
                </div>

                <div id="qrError" class="alert alert-danger d-none">
                    Unable to load QR Code.
                </div>

            </div>

            <div class="modal-footer justify-content-center">

                <button type="button"
                        class="btn btn-primary"
                        id="downloadQrJpg"
                        disabled>
                    <i class="bi bi-download me-1"></i>
                    Download JPG
                </button>

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>
    </div>
</div>