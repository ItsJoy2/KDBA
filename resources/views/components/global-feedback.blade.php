
{{-- Success Message --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3"
         role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
@endif

{{-- Error Message --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3"
         role="alert">
        <i class="bi bi-x-circle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
@endif

{{-- Warning Message --}}
@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 rounded-3"
         role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3"
         role="alert">
        <strong><i class="bi bi-exclamation-circle me-2"></i>Please fix the following errors:</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
@endif

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1"
     aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">

            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-trash3-fill display-3"></i>
                </div>

                <h5 class="fw-bold" id="deleteConfirmModalLabel">
                    Are you sure?
                </h5>

                <p class="text-muted small mb-4" id="deleteConfirmMessage">
                    Do you really want to delete this record?
                    This action cannot be undone.
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-danger"
                            id="confirmDeleteBtn">
                        <i class="bi bi-trash3 me-1"></i> Yes, Delete
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss alerts after 5 seconds.
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            if (alert.isConnected) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 5000);
    });

    const modalElement = document.getElementById('deleteConfirmModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const messageElement = document.getElementById('deleteConfirmMessage');

    if (!modalElement || !confirmBtn || !window.bootstrap) {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    let currentForm = null;
    let submitting = false;

    // Open modal when a delete form is submitted.
    document.addEventListener('submit', function (event) {
        const form = event.target;

        if (!form.matches('form.js-delete-confirm')) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();

        if (currentForm || submitting) {
            return;
        }

        currentForm = form;

        messageElement.textContent =
            form.dataset.deleteMessage ||
            'Do you really want to delete this record? This action cannot be undone.';

        modal.show();
    });

    // Confirm deletion.
    confirmBtn.addEventListener('click', function () {
        if (!currentForm || submitting) {
            return;
        }

        if (!currentForm.reportValidity()) {
            return;
        }

        submitting = true;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        currentForm.dataset.confirmed = 'true';

        // Submit original form with CSRF and DELETE method.
        currentForm.requestSubmit();
    });

    // Reset state when the modal is cancelled.
    modalElement.addEventListener('hidden.bs.modal', function () {
        if (!submitting) {
            currentForm = null;
            confirmBtn.disabled = false;
            confirmBtn.innerHTML =
                '<i class="bi bi-trash3 me-1"></i> Yes, Delete';
        }
    });

    // Reset after browser restores a page from its cache.
    window.addEventListener('pageshow', function () {
        currentForm = null;
        submitting = false;
        confirmBtn.disabled = false;
        confirmBtn.innerHTML =
            '<i class="bi bi-trash3 me-1"></i> Yes, Delete';
    });
});
</script>
