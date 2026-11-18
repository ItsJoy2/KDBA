@extends('layouts.app')

@section('title', 'Membership Fee Settings')

@section('content')
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Membership Fee Settings</h1>
                </div>

                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Membership Fee Settings
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            @include('components.global-feedback')

            {{-- Page Heading --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <p class="text-muted mb-0">
                        Manage admission, membership, annual fund and chamber fees.
                    </p>
                </div>
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>

                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.membership-fees.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm">

                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-sliders me-2"></i>
                            Fee Settings
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            {{-- Sidebar Tabs --}}
                            <div class="col-12 col-lg-3">
                                <div class="list-group" id="feeSettingsTabs" role="tablist">

                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action active text-start"
                                        id="membership-tab"
                                        data-bs-toggle="list"
                                        data-bs-target="#membership-pane"
                                        role="tab"
                                        aria-controls="membership-pane"
                                        aria-selected="true">

                                        <i class="bi bi-person-badge me-2"></i>
                                        Membership Fees
                                    </button>

                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action text-start"
                                        id="annual-funds-tab"
                                        data-bs-toggle="list"
                                        data-bs-target="#annual-funds-pane"
                                        role="tab"
                                        aria-controls="annual-funds-pane"
                                        aria-selected="false">

                                        <i class="bi bi-bank me-2"></i>
                                        Annual Funds
                                    </button>

                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action text-start"
                                        id="apprentice-tab"
                                        data-bs-toggle="list"
                                        data-bs-target="#apprentice-pane"
                                        role="tab"
                                        aria-controls="apprentice-pane"
                                        aria-selected="false">

                                        <i class="bi bi-mortarboard me-2"></i>
                                        Apprentice Fees
                                    </button>

                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action text-start"
                                        id="assistant-tab"
                                        data-bs-toggle="list"
                                        data-bs-target="#assistant-pane"
                                        role="tab"
                                        aria-controls="assistant-pane"
                                        aria-selected="false">

                                        <i class="bi bi-person-workspace me-2"></i>
                                        Lawyer Assistant
                                    </button>

                                    <button
                                        type="button"
                                        class="list-group-item list-group-item-action text-start"
                                        id="chamber-tab"
                                        data-bs-toggle="list"
                                        data-bs-target="#chamber-pane"
                                        role="tab"
                                        aria-controls="chamber-pane"
                                        aria-selected="false">

                                        <i class="bi bi-building me-2"></i>
                                        Chamber Rent
                                    </button>

                                </div>
                            </div>

                            {{-- Tab Content --}}
                            <div class="col-12 col-lg-9">
                                <div class="tab-content" id="feeSettingsTabContent">

                                    {{-- Membership Fees --}}
                                    <div
                                        class="tab-pane fade show active"
                                        id="membership-pane"
                                        role="tabpanel"
                                        aria-labelledby="membership-tab"
                                        tabindex="0">

                                        <div class="mb-4">
                                            <h5 class="mb-1">Membership Admission Fees</h5>
                                            <p class="text-muted small mb-0">
                                                Configure one-time admission fees for new members.
                                            </p>
                                        </div>

                                        <div class="row g-3">

                                            {{-- Mother Member --}}
                                            <div class="col-12 col-md-6">
                                                <label for="mother_admission" class="form-label">
                                                    Mother Member Admission Fee

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="One-time admission fee for a new mother member."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="mother_admission"
                                                        name="fees[membership.admission.mother_member]"
                                                        class="form-control @error('fees.membership.admission.mother_member') is-invalid @enderror"
                                                        value="{{ old('fees.membership.admission.mother_member', $settings->get('membership.admission.mother_member', 50000)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.membership.admission.mother_member')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Sub Member --}}
                                            <div class="col-12 col-md-6">
                                                <label for="sub_admission" class="form-label">
                                                    Sub Member Admission Fee

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="One-time admission fee for a new sub member."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="sub_admission"
                                                        name="fees[membership.admission.sub_member]"
                                                        class="form-control @error('fees.membership.admission.sub_member') is-invalid @enderror"
                                                        value="{{ old('fees.membership.admission.sub_member', $settings->get('membership.admission.sub_member', 100000)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.membership.admission.sub_member')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Monthly Membership --}}
                                            <div class="col-12 col-md-6">
                                                <label for="monthly_membership" class="form-label">
                                                    Monthly Membership Fee

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Monthly recurring membership fee for regular members."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="monthly_membership"
                                                        name="fees[membership.recurring.monthly_membership]"
                                                        class="form-control @error('fees.membership.recurring.monthly_membership') is-invalid @enderror"
                                                        value="{{ old('fees.membership.recurring.monthly_membership', $settings->get('membership.recurring.monthly_membership', 100)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.membership.recurring.monthly_membership')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- Annual Funds --}}
                                    <div
                                        class="tab-pane fade"
                                        id="annual-funds-pane"
                                        role="tabpanel"
                                        aria-labelledby="annual-funds-tab"
                                        tabindex="0">

                                        <div class="mb-4">
                                            <h5 class="mb-1">Annual Membership Funds</h5>
                                            <p class="text-muted small mb-0">
                                                Configure yearly fees and welfare contributions.
                                            </p>
                                        </div>

                                        <div class="row g-3">

                                            @php
                                                $annualFees = [
                                                    'membership.recurring.annual_building_fund' => [
                                                        'label' => 'Building Fund',
                                                        'default' => 100,
                                                        'description' => 'Annual contribution towards the Bar Association building fund.',
                                                    ],
                                                    'membership.recurring.annual_library_fee' => [
                                                        'label' => 'Library Fee',
                                                        'default' => 100,
                                                        'description' => 'Annual library maintenance and facility fee.',
                                                    ],
                                                    'membership.recurring.annual_library_card_fee' => [
                                                        'label' => 'Library Card Fee',
                                                        'default' => 100,
                                                        'description' => 'Annual library card fee for members.',
                                                    ],
                                                    'membership.recurring.annual_relief_fund' => [
                                                        'label' => 'Relief Fund',
                                                        'default' => 100,
                                                        'description' => 'Annual contribution towards the member relief fund.',
                                                    ],
                                                    'membership.recurring.annual_education_fund' => [
                                                        'label' => 'Education / Scholarship Fund',
                                                        'default' => 100,
                                                        'description' => 'Annual contribution towards education and scholarship support.',
                                                    ],
                                                    'membership.recurring.annual_retirement_fund' => [
                                                        'label' => 'Retirement Fund',
                                                        'default' => 100,
                                                        'description' => 'Annual contribution towards the retirement fund.',
                                                    ],
                                                    'membership.recurring.annual_mb_fund' => [
                                                        'label' => 'M.B. Fund',
                                                        'default' => 2000,
                                                        'description' => 'Annual M.B. fund contribution.',
                                                    ],
                                                ];
                                            @endphp

                                            @foreach ($annualFees as $key => $fee)
                                                <div class="col-12 col-md-6">
                                                    <label for="fee_{{ $loop->index }}" class="form-label">
                                                        {{ $fee['label'] }}

                                                        <i class="bi bi-info-circle text-muted ms-1"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="{{ $fee['description'] }}"></i>
                                                    </label>

                                                    <div class="input-group">
                                                        <span class="input-group-text">৳</span>

                                                        <input
                                                            type="number"
                                                            id="fee_{{ $loop->index }}"
                                                            name="fees[{{ $key }}]"
                                                            class="form-control @error('fees.' . $key) is-invalid @enderror"
                                                            value="{{ old('fees.' . $key, $settings->get($key, $fee['default'])) }}"
                                                            min="0"
                                                            max="999999999.99"
                                                            step="0.01"
                                                            required>

                                                        @error('fees.' . $key)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>

                                    {{-- Apprentice Fees --}}
                                    <div
                                        class="tab-pane fade"
                                        id="apprentice-pane"
                                        role="tabpanel"
                                        aria-labelledby="apprentice-tab"
                                        tabindex="0">

                                        <div class="mb-4">
                                            <h5 class="mb-1">Apprentice Fees</h5>
                                            <p class="text-muted small mb-0">
                                                Configure apprentice admission and card charges.
                                            </p>
                                        </div>

                                        <div class="row g-3">

                                            {{-- Apprentice Admission --}}
                                            <div class="col-12 col-md-6">
                                                <label for="apprentice_admission" class="form-label">
                                                    Apprentice Admission Fee

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="One-time admission fee for a new apprentice."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="apprentice_admission"
                                                        name="fees[apprentice.admission_fee]"
                                                        class="form-control @error('fees.apprentice.admission_fee') is-invalid @enderror"
                                                        value="{{ old('fees.apprentice.admission_fee', $settings->get('apprentice.admission_fee', 1000)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.apprentice.admission_fee')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Apprentice Card --}}
                                            <div class="col-12 col-md-6">
                                                <label for="apprentice_card" class="form-label">
                                                    Apprentice Card Fee

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Card issuance fee for an apprentice."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="apprentice_card"
                                                        name="fees[apprentice.card_fee]"
                                                        class="form-control @error('fees.apprentice.card_fee') is-invalid @enderror"
                                                        value="{{ old('fees.apprentice.card_fee', $settings->get('apprentice.card_fee', 500)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.apprentice.card_fee')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- Lawyer Assistant --}}
                                    <div
                                        class="tab-pane fade"
                                        id="assistant-pane"
                                        role="tabpanel"
                                        aria-labelledby="assistant-tab"
                                        tabindex="0">

                                        <div class="mb-4">
                                            <h5 class="mb-1">Lawyer Assistant Fees</h5>
                                            <p class="text-muted small mb-0">
                                                Configure lawyer assistant card renewal charges.
                                            </p>
                                        </div>

                                        <div class="row g-3">

                                            <div class="col-12 col-md-6">
                                                <label for="assistant_card_renewal" class="form-label">
                                                    Card Renewal Fee

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Renewal fee charged when a lawyer assistant renews their card."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="assistant_card_renewal"
                                                        name="fees[lawyer_assistant.card_renewal_fee]"
                                                        class="form-control @error('fees.lawyer_assistant.card_renewal_fee') is-invalid @enderror"
                                                        value="{{ old('fees.lawyer_assistant.card_renewal_fee', $settings->get('lawyer_assistant.card_renewal_fee', 200)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.lawyer_assistant.card_renewal_fee')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    {{-- Chamber Rent --}}
                                    <div
                                        class="tab-pane fade"
                                        id="chamber-pane"
                                        role="tabpanel"
                                        aria-labelledby="chamber-tab"
                                        tabindex="0">

                                        <div class="mb-4">
                                            <h5 class="mb-1">Chamber Rent Configuration</h5>
                                            <p class="text-muted small mb-0">
                                                Configure monthly chamber rent per person.
                                            </p>
                                        </div>

                                        <div class="row g-3">

                                            {{-- Standard Chamber --}}
                                            <div class="col-12 col-md-6">
                                                <label for="chamber_standard" class="form-label">
                                                    Standard Chamber (Monthly)

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Monthly chamber rent per person for a standard chamber."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="chamber_standard"
                                                        name="fees[chamber.rent.standard_monthly]"
                                                        class="form-control @error('fees.chamber.rent.standard_monthly') is-invalid @enderror"
                                                        value="{{ old('fees.chamber.rent.standard_monthly', $settings->get('chamber.rent.standard_monthly', 500)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.chamber.rent.standard_monthly')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Joint Chamber --}}
                                            <div class="col-12 col-md-6">
                                                <label for="chamber_joint" class="form-label">
                                                    Joint Chamber (Monthly)

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Monthly chamber rent per person for a joint chamber."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="chamber_joint"
                                                        name="fees[chamber.rent.joint_monthly]"
                                                        class="form-control @error('fees.chamber.rent.joint_monthly') is-invalid @enderror"
                                                        value="{{ old('fees.chamber.rent.joint_monthly', $settings->get('chamber.rent.joint_monthly', 500)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.chamber.rent.joint_monthly')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- AC First Six Months --}}
                                            <div class="col-12 col-md-6">
                                                <label for="chamber_ac_first" class="form-label">
                                                    AC Chamber - First 6 Months

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Monthly AC chamber rent per person for the first six months."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="chamber_ac_first"
                                                        name="fees[chamber.rent.ac.first_six_months]"
                                                        class="form-control @error('fees.chamber.rent.ac.first_six_months') is-invalid @enderror"
                                                        value="{{ old('fees.chamber.rent.ac.first_six_months', $settings->get('chamber.rent.ac.first_six_months', 500)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.chamber.rent.ac.first_six_months')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- AC Remaining Six Months --}}
                                            <div class="col-12 col-md-6">
                                                <label for="chamber_ac_remaining" class="form-label">
                                                    AC Chamber - Remaining 6 Months

                                                    <i class="bi bi-info-circle text-muted ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Monthly AC chamber rent per person for the remaining six months."></i>
                                                </label>

                                                <div class="input-group">
                                                    <span class="input-group-text">৳</span>

                                                    <input
                                                        type="number"
                                                        id="chamber_ac_remaining"
                                                        name="fees[chamber.rent.ac.remaining_six_months]"
                                                        class="form-control @error('fees.chamber.rent.ac.remaining_six_months') is-invalid @enderror"
                                                        value="{{ old('fees.chamber.rent.ac.remaining_six_months', $settings->get('chamber.rent.ac.remaining_six_months', 1000)) }}"
                                                        min="0"
                                                        max="999999999.99"
                                                        step="0.01"
                                                        required>

                                                    @error('fees.chamber.rent.ac.remaining_six_months')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>

                                        {{-- AC Annual Summary --}}
                                        {{-- <div class="alert alert-light border mt-4 mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-calculator text-primary me-2 mt-1"></i>

                                                <div>
                                                    <strong>AC Chamber Annual Calculation</strong>

                                                    <div class="small text-muted">
                                                        First 6 months + remaining 6 months.
                                                        With the default rates:
                                                        (৳500 × 6) + (৳1,000 × 6) = ৳9,000 per person annually.
                                                    </div>
                                                </div>
                                            </div>
                                        </div> --}}

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">

                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Existing invoices will not be changed.
                        </small>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Save Fee Settings
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Bootstrap tooltips.
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
                bootstrap.Tooltip.getOrCreateInstance(element);
            });

            // Restore the tab containing validation errors.
            const firstInvalidInput = document.querySelector('.is-invalid');

            if (firstInvalidInput) {
                const tabPane = firstInvalidInput.closest('.tab-pane');

                if (tabPane && tabPane.id) {
                    const tabButton = document.querySelector(
                        '[data-bs-target="#' + tabPane.id + '"]'
                    );

                    if (tabButton) {
                        bootstrap.Tab.getOrCreateInstance(tabButton).show();
                    }
                }
            }
        });
    </script>
@endpush
