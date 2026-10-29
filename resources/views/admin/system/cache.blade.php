
@extends('layouts.app')

@section('title', 'Cache Management')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-12">

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Cache Management
                    </h3>
                </div>

                <div class="card-body">

                    {{-- Global Feedback and Confirmation Modal --}}
                    @include('components.global-feedback')

                    {{-- Warning --}}
                    <div class="alert alert-warning shadow-sm border-0 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Clearing the cache may require cached configuration,
                        routes, views, and application cache to be regenerated.
                    </div>

                    <p class="text-muted mb-4">
                        Use the button below to clear Laravel application
                        cache and optimization caches.
                    </p>

                    {{-- Cache Progress and Action --}}
                    <div class="row align-items-start">

                        {{-- Progress Bar --}}
                        <div id="cacheProgressArea" class="col-md-9 col-12 mb-4">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span
                                    id="cacheProgressLabel"
                                    class="fw-semibold"
                                >
                                    Ready to clear cache
                                </span>

                                <span
                                    id="cacheProgressPercent"
                                    class="fw-bold"
                                >
                                    0%
                                </span>
                            </div>

                            <div
                                class="progress"
                                id="cacheProgressBarWrapper"
                                role="progressbar"
                                aria-label="Cache clearing progress"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="0"
                                style="height: 24px;"
                            >
                                <div
                                    id="cacheProgressBar"
                                    class="progress-bar progress-bar-striped bg-danger"
                                    style="width: 0%; transition: width 0.2s ease;"
                                ></div>
                            </div>

                        </div>

                        {{-- Cache Clear Button --}}
                        <div class="col-md-3 col-12 mt-4">

                            <button
                                type="button"
                                id="clearCacheBtn"
                                class="btn btn-danger w-100"
                            >
                                <i class="bi bi-trash3-fill me-1"></i>
                                Clear Application Cache
                            </button>

                            <div
                                id="cacheLoading"
                                class="d-none text-muted mt-2 text-center"
                            >
                                <span
                                    class="spinner-border spinner-border-sm text-danger me-1"
                                    role="status"
                                    aria-hidden="true"
                                ></span>

                                Please wait, clearing cache...
                            </div>

                        </div>

                    </div>

                    {{-- AJAX Feedback --}}
                    <div
                        id="cacheAjaxFeedback"
                        class="mt-2"
                        role="status"
                        aria-live="polite"
                    ></div>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const button = document.getElementById('clearCacheBtn');
    const progressBar = document.getElementById('cacheProgressBar');
    const progressWrapper = document.getElementById('cacheProgressBarWrapper');
    const progressPercent = document.getElementById('cacheProgressPercent');
    const progressLabel = document.getElementById('cacheProgressLabel');
    const loading = document.getElementById('cacheLoading');
    const feedback = document.getElementById('cacheAjaxFeedback');

    const modalElement = document.getElementById('deleteConfirmModal');
    const confirmButton = document.getElementById('confirmDeleteBtn');
    const messageElement = document.getElementById('deleteConfirmMessage');

    if (
        !button ||
        !progressBar ||
        !modalElement ||
        !confirmButton ||
        !messageElement ||
        !window.bootstrap
    ) {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

    let progress = 0;
    let progressTimer = null;
    let requestRunning = false;
    let confirmationPending = false;

    /*
    |--------------------------------------------------------------------------
    | Progress
    |--------------------------------------------------------------------------
    */

    function updateProgress(value, label) {
        progress = Math.max(0, Math.min(100, value));

        progressBar.style.width = progress + '%';
        progressPercent.textContent = Math.floor(progress) + '%';

        progressWrapper.setAttribute(
            'aria-valuenow',
            Math.floor(progress)
        );

        progressLabel.textContent = label;
    }

    function stopProgress() {
        if (progressTimer !== null) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Feedback
    |--------------------------------------------------------------------------
    */

    function showFeedback(type, message) {
        const isSuccess = type === 'success';

        const alert = document.createElement('div');

        alert.className = [
            'alert',
            isSuccess ? 'alert-success' : 'alert-danger',
            'alert-dismissible',
            'fade',
            'show',
            'shadow-sm',
            'border-0',
            'rounded-3'
        ].join(' ');

        alert.setAttribute('role', 'alert');

        const icon = document.createElement('i');

        icon.className = isSuccess
            ? 'bi bi-check-circle-fill me-2'
            : 'bi bi-x-circle-fill me-2';

        const messageText = document.createElement('span');
        messageText.textContent = message;

        const closeButton = document.createElement('button');

        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        closeButton.setAttribute('aria-label', 'Close');

        alert.appendChild(icon);
        alert.appendChild(messageText);
        alert.appendChild(closeButton);

        // Display feedback above the progress bar.
        feedback.replaceChildren();

        const progressArea = document.getElementById('cacheProgressArea');

        progressArea.parentNode.insertBefore(alert, progressArea);

        setTimeout(function () {
            if (alert.isConnected) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 5000);
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmation Modal
    |--------------------------------------------------------------------------
    */

    button.addEventListener('click', function () {
        if (requestRunning || confirmationPending) {
            return;
        }

        confirmationPending = true;

        messageElement.textContent =
            'Are you sure you want to clear the application cache? ' +
            'Cached configuration, routes, views, and application cache ' +
            'may need to be regenerated.';

        // Customize the shared modal for cache clearing.
        confirmButton.innerHTML =
            '<i class="bi bi-trash3 me-1"></i> Yes, Clear Cache';

        confirmButton.classList.remove('btn-danger');
        confirmButton.classList.add('btn-primary');

        modal.show();
    });

    /*
    |--------------------------------------------------------------------------
    | Confirm Cache Clear
    |--------------------------------------------------------------------------
    */

    confirmButton.addEventListener('click', async function (event) {

        if (!confirmationPending || requestRunning) {
            return;
        }

        // Prevent the global delete handler from submitting a delete form.
        event.preventDefault();
        event.stopImmediatePropagation();

        confirmationPending = false;
        requestRunning = true;

        confirmButton.disabled = true;
        button.disabled = true;

        modal.hide();

        loading.classList.remove('d-none');
        feedback.replaceChildren();

        progressBar.classList.remove('bg-success');
        progressBar.classList.add(
            'bg-danger',
            'progress-bar-animated'
        );

        updateProgress(0, 'Starting cache clear...');

        /*
         * Simulated progress.
         * It will not reach 100% until the server confirms success.
         */
        progressTimer = setInterval(function () {

            if (progress < 90) {
                updateProgress(
                    Math.min(
                        90,
                        progress + Math.max(1, (90 - progress) * 0.08)
                    ),
                    'Clearing application cache...'
                );
            } else if (progress < 95) {
                updateProgress(
                    Math.min(95, progress + 0.2),
                    'Waiting for server response...'
                );
            }

        }, 150);

        try {
            const response = await fetch(
                @json(route('system.cache.clear')),
                {
                    method: 'POST',
                    credentials: 'same-origin',

                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'X-Requested-With': 'XMLHttpRequest'
                    },

                    body: JSON.stringify({})
                }
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Unable to clear application cache.'
                );
            }

            stopProgress();

            updateProgress(
                100,
                'Cache cleared successfully!'
            );

            progressBar.classList.remove(
                'bg-danger',
                'progress-bar-animated'
            );

            progressBar.classList.add('bg-success');

            showFeedback(
                'success',
                data.message || 'Application cache cleared successfully.'
            );

        } catch (error) {

            stopProgress();

            progressBar.classList.remove(
                'bg-success',
                'progress-bar-animated'
            );

            progressBar.classList.add('bg-danger');

            updateProgress(
                progress,
                'Cache clear failed.'
            );

            showFeedback(
                'error',
                error.message || 'Something went wrong while clearing the cache.'
            );

        } finally {

            requestRunning = false;

            button.disabled = false;
            confirmButton.disabled = false;

            loading.classList.add('d-none');

            // Restore the global modal's default Delete button design.
            confirmButton.innerHTML =
                '<i class="bi bi-trash3 me-1"></i> Yes, Delete';

            confirmButton.classList.remove('btn-primary');
            confirmButton.classList.add('btn-danger');
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Reset Modal State
    |--------------------------------------------------------------------------
    */

    modalElement.addEventListener('hidden.bs.modal', function () {

        if (!requestRunning) {
            confirmationPending = false;
            confirmButton.disabled = false;

            confirmButton.innerHTML =
                '<i class="bi bi-trash3 me-1"></i> Yes, Delete';

            confirmButton.classList.remove('btn-primary');
            confirmButton.classList.add('btn-danger');
        }
    });

});
</script>
@endpush
