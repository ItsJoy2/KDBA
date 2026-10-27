@php
    $editing = isset($member);
    $currentUser = auth()->user();

    $memberStatuses = [
        'member' => 'Member',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'deceased' => 'Deceased',
        'life_member' => 'Life Member',
        'apprentice' => 'Apprentice',
        'lawyer_assistant' => 'Lawyer Assistant',
        'office_staff' => 'Office Staff',
    ];

    $approvalStatuses = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    $selected = fn ($key, $default = '') => old($key, $member->$key ?? $default);
@endphp

@include('components.global-feedback')


<style>
    .wizard-steps {
        counter-reset: step;
        list-style: none;
        padding: 0;
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 1rem;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--bs-border-color);
        z-index: 0;
    }

    .wizard-steps li {
        position: relative;
        z-index: 1;
        background: var(--bs-body-bg);
        padding: 0 .75rem;
        text-align: center;
        color: var(--bs-secondary-color);
        font-size: .875rem;
    }

    .wizard-steps li::before {
        counter-increment: step;
        content: counter(step);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        margin: 0 auto .5rem;
        border-radius: 50%;
        background: var(--bs-tertiary-bg);
        border: 2px solid var(--bs-border-color);
        color: var(--bs-secondary-color);
        font-weight: 600;
    }

    .wizard-steps li.active {
        color: var(--bs-primary);
        font-weight: 600;
    }

    .wizard-steps li.active::before {
        background: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }

    .wizard-steps li.completed::before {
        background: var(--bs-success);
        border-color: var(--bs-success);
        color: #fff;
        content: '\f633';
        font-family: 'bootstrap-icons';
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-8">
        <div class="card">
            <div class="card-body p-4">

                {{-- Step indicators --}}
                <ol class="wizard-steps mb-4" id="wizard-steps">
                    <li class="active" data-step="0">Account</li>
                    <li data-step="1">Profile</li>
                    <li data-step="2">Preferences</li>
                    <li data-step="3">Review</li>
                </ol>

                {{-- Form --}}

                <form id="wizard-form" action="{{ $editing ? route('users.update', $member) : route('users.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    @if ($editing)
                        @method('PUT')
                    @endif


                    {{-- Step 1 --}}
                    <fieldset class="wizard-step" data-step="0">
                        <h2 class="h5 mb-3">Create your account</h2>

                        <div class="row g-3">
                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label" for="wz-email">
                                Email
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="wz-email"
                                name="email"
                                value="{{ old('email', $member->email ?? '') }}"
                                maxlength="255"
                                autocomplete="email"
                            >

                            <div class="invalid-feedback" id="email-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>

                        {{-- Mobile --}}
                        <div class="col-md-6">
                            <label class="form-label" for="wz-mobile">
                                Mobile number <span class="text-danger">*</span>
                            </label>

                            <input
                                type="tel"
                                class="form-control"
                                id="wz-mobile"
                                name="mobile"
                                value="{{ old('mobile', $member->mobile ?? '') }}"
                                maxlength="30"
                                autocomplete="tel"
                                required
                            >

                            <div class="invalid-feedback" id="mobile-feedback">
                                Mobile number is required.
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="col-md-6">
                            <label class="form-label" for="wz-password">
                                Password
                                @if (!$editing)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="wz-password"
                                name="password"
                                minlength="8"
                                autocomplete="new-password"
                                {{ !$editing ? 'required' : '' }}
                            >

                            <div class="form-text">
                                <div id="password-length" class="text-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    At least 8 characters
                                </div>

                                <div id="password-uppercase" class="text-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    At least one uppercase letter (A-Z)
                                </div>

                                <div id="password-lowercase" class="text-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    At least one lowercase letter (a-z)
                                </div>

                                <div id="password-number" class="text-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    At least one number (0-9)
                                </div>

                                <div id="password-symbol" class="text-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    At least one symbol (!@#$...)
                                </div>
                            </div>

                            @if ($editing)
                                <small class="text-muted">
                                    Leave blank to keep the current password.
                                </small>
                            @endif
                        </div>

                        {{-- Confirm Password --}}
                        <div class="col-md-6">
                            <label class="form-label" for="wz-password2">
                                Confirm password
                                @if (!$editing)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="wz-password2"
                                name="password_confirmation"
                                autocomplete="new-password"
                                {{ !$editing ? 'required' : '' }}
                            >

                            <div class="invalid-feedback" id="password-confirm-feedback">
                                Passwords do not match.
                            </div>
                        </div>
                        </div>
                    </fieldset>

                    {{-- Step 2 --}}
                    <fieldset class="wizard-step d-none" data-step="1">
                        <h2 class="h5 mb-3">Personal Information</h2>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="wz-full-name">
                                    Full name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control"
                                    id="wz-full-name" name="full_name"
                                    value="{{ old('full_name', $member->full_name ?? '') }}"
                                    maxlength="150" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-father">
                                    Father's name
                                </label>
                                <input type="text" class="form-control"
                                    id="wz-father" name="father_name"
                                    value="{{ old('father_name', $member->father_name ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-mother">
                                    Mother's name
                                </label>
                                <input type="text" class="form-control"
                                    id="wz-mother" name="mother_name"
                                    value="{{ old('mother_name', $member->mother_name ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-dob">
                                    Date of birth
                                </label>
                                <input type="date" class="form-control"
                                    id="wz-dob" name="dob"
                                    value="{{ old('dob', isset($member) && $member->dob ? \Illuminate\Support\Carbon::parse($member->dob)->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-gender">Gender</label>
                                <select class="form-select" id="wz-gender" name="gender">
                                    <option value="">Select gender</option>
                                    @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('gender', $member->gender ?? '') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-blood">Blood group</label>
                                <select class="form-select" id="wz-blood" name="blood_group">
                                    <option value="">Select blood group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                                        <option value="{{ $group }}"
                                            @selected(old('blood_group', $member->blood_group ?? '') === $group)>
                                            {{ $group }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-present-address">
                                    Present address
                                </label>
                                <textarea class="form-control" id="wz-present-address"
                                        name="present_address" rows="3">{{ old('present_address', $member->present_address ?? '') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-permanent-address">
                                    Permanent address
                                </label>
                                <textarea class="form-control" id="wz-permanent-address"
                                        name="permanent_address" rows="3">{{ old('permanent_address', $member->permanent_address ?? '') }}</textarea>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Step 3 --}}
                    <fieldset class="wizard-step d-none" data-step="2">
                        <h2 class="h5 mb-3">Membership Information</h2>

                        <div class="row g-3">
                            {{-- Member Status --}}
                            <div class="col-md-6">
                                <label class="form-label" for="member_status">
                                    Member Status <span class="text-danger">*</span>
                                </label>

                                <select
                                    class="form-select"
                                    id="member_status"
                                    name="member_status"
                                    required
                                >
                                    <option value="">Select member status</option>

                                    @foreach($memberStatuses as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected($selected('member_status', 'member') === $value)
                                        >
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('member_status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Assigned Lawyer --}}
                            <div
                                class="col-md-6"
                                id="lawyer-assignment-wrapper"
                            >
                                <label class="form-label" for="lawyer_id">
                                    Assigned Senior Lawyer / Advocate
                                </label>

                                <select
                                    class="form-select"
                                    name="lawyer_id"
                                    id="lawyer_id"
                                >
                                    <option value="">Select Senior Lawyer / Advocate</option>

                                    @foreach($lawyers as $lawyer)
                                        <option
                                            value="{{ $lawyer->id }}"
                                            @selected(
                                                (string) $selected(
                                                    'lawyer_id',
                                                    $member->lawyer_id ?? ''
                                                ) === (string) $lawyer->id
                                            )
                                        >
                                            {{ $lawyer->full_name }}
                                            @if($lawyer->membership_number)
                                                — {{ $lawyer->membership_number }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                @error('lawyer_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-membership">
                                    Membership number
                                </label>
                                <input type="text" class="form-control"
                                    id="wz-membership" name="membership_number"
                                    value="{{ old('membership_number', $member->membership_number ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-bar-date">
                                    Bar joining date
                                </label>
                                <input type="date" class="form-control"
                                    id="wz-bar-date" name="bar_join_date"
                                    value="{{ old('bar_join_date', isset($member) && $member->bar_join_date ? \Illuminate\Support\Carbon::parse($member->bar_join_date)->format('Y-m-d') : '') }}">
                            </div>

                            {{-- <div class="col-md-6">
                                <label class="form-label" for="wz-sanad">
                                    Sanad number
                                </label>
                                <input type="text" class="form-control"
                                    id="wz-sanad" name="sanad_number"
                                    value="{{ old('sanad_number', $member->sanad_number ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-sanad-date">
                                    Sanad date
                                </label>
                                <input type="date" class="form-control"
                                    id="wz-sanad-date" name="sanad_date"
                                    value="{{ old('sanad_date', isset($member) && $member->sanad_date ? \Illuminate\Support\Carbon::parse($member->sanad_date)->format('Y-m-d') : '') }}">
                            </div> --}}

                            <div class="col-md-6">
                                <label class="form-label" for="wz-nid">
                                    NID / Passport number
                                </label>
                                <input type="text" class="form-control"
                                    id="wz-nid" name="nid_passport_number"
                                    value="{{ old('nid_passport_number', $member->nid_passport_number ?? '') }}" accept=".jpg,.jpeg,.png,">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-photo">
                                    Member photo
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    id="wz-photo"
                                    name="photo"
                                    accept=".jpg,.jpeg,.png"
                                >

                                @if ($editing && !empty($photoUrl))
                                    <div class="mt-2">
                                        <img
                                            src="{{ $photoUrl }}"
                                            alt="Current member photo"
                                            class="img-thumbnail"
                                            style="width: 180px; height: 180px; object-fit: contain;"
                                        >

                                        <div class="small text-muted mt-1">
                                            Current member photo
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-front">
                                    NID / Passport front
                                </label>
                                <input type="file" class="form-control"
                                    id="wz-front" name="nid_images[front]"
                                    accept=".jpg,.jpeg,.png">
                                @if ($editing && !empty($nidImages['front']))
                                    <div class="mt-2">
                                        <img
                                            src="{{ $nidImages['front'] }}"
                                            alt="Current NID front"
                                            class="img-thumbnail"
                                            style="max-width: 260px; max-height: 180px; object-fit: contain;"
                                        >
                                        <div class="small text-muted mt-1">Current NID front</div>
                                    </div>
                                @endif
                                @if(session('temp_nid_front'))
                                    <img
                                        data-retained-image="nid_images[front]"
                                        src="{{ asset('storage/' . session('temp_nid_front')) }}"
                                        alt="Previously uploaded photo"
                                        hidden
                                    >
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="wz-back">
                                    NID / Passport back
                                </label>
                                <input type="file" class="form-control"
                                    id="wz-back" name="nid_images[back]"
                                    accept=".jpg,.jpeg,.png,">
                                @if ($editing && !empty($nidImages['back']))
                                    <div class="mt-2">
                                        <img
                                            src="{{ $nidImages['back'] }}"
                                            alt="Current NID back"
                                            class="img-thumbnail"
                                            style="max-width: 260px; max-height: 180px; object-fit: contain;"
                                        >
                                        <div class="small text-muted mt-1">Current NID back</div>
                                    </div>
                                @endif
                                @if(session('temp_nid_back'))
                                    <img
                                        data-retained-image="nid_images[back]"
                                        src="{{ asset('storage/' . session('temp_nid_back')) }}"
                                        alt="Previously uploaded photo"
                                        hidden
                                    >
                                @endif
                            </div>

                        </div>
                    </fieldset>

                    {{-- Step 4 --}}
                    <fieldset class="wizard-step d-none" data-step="3">

                        <h2 class="h5 mb-3">Review &amp; confirm</h2>

                        <div id="wz-summary" class="row g-3"></div>

                        <div class="alert alert-light border mt-3">
                            Please review all member information before submitting.
                        </div>
                    </fieldset>

                    {{-- Navigation --}}
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                id="wz-prev" disabled>
                            <i class="bi bi-arrow-left me-1"
                               aria-hidden="true"></i>
                            Previous
                        </button>

                        <button type="button" class="btn btn-primary"
                                id="wz-next">
                            Next
                            <i class="bi bi-arrow-right ms-1"
                               aria-hidden="true"></i>
                        </button>

                        <button type="submit"
                                class="btn btn-success d-none"
                                id="wz-submit">
                            <i class="bi bi-check-lg me-1"
                               aria-hidden="true"></i>
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('wizard-form');
    if (!form) return;

    const editing = @json($editing);

    // Existing images (edit mode) keyed by input name
    const existingImages = {
        'photo': @json($photoUrl ?? null),
        'nid_images[front]': @json($nidImages['front'] ?? null),
        'nid_images[back]': @json($nidImages['back'] ?? null)
    };

    const steps = Array.from(form.querySelectorAll('.wizard-step'));
    const indicators = document.querySelectorAll('#wizard-steps li');
    const prevBtn = document.getElementById('wz-prev');
    const nextBtn = document.getElementById('wz-next');
    const submitBtn = document.getElementById('wz-submit');
    const summary = document.getElementById('wz-summary');

    const email = document.getElementById('wz-email');
    const mobile = document.getElementById('wz-mobile');
    const password = document.getElementById('wz-password');
    const confirmPassword = document.getElementById('wz-password2');

    const customFields = [email, mobile, password, confirmPassword];
    const excludedFields = ['password', 'password_confirmation', '_token', '_method'];

    let currentStep = 0;
    let blobUrls = [];

    /* ---------------------------------------------------------
     * Field validators (step 1)
     * ------------------------------------------------------- */
    const passwordRules = [
        { id: 'password-length',    test: v => v.length >= 8 },
        { id: 'password-uppercase', test: v => /[A-Z]/.test(v) },
        { id: 'password-lowercase', test: v => /[a-z]/.test(v) },
        { id: 'password-number',    test: v => /[0-9]/.test(v) },
        { id: 'password-symbol',    test: v => /[^A-Za-z0-9]/.test(v) }
    ];

    function setFieldState(field, valid, message = '') {
        field.classList.toggle('is-invalid', !valid);
        field.classList.toggle('is-valid', valid);

        if (!valid && message) {
            const feedback = field.parentElement.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = message;
        }
    }

    function clearState(field) {
        field.classList.remove('is-invalid', 'is-valid');
    }

    function validateEmail() {
        const value = email.value.trim();

        // Email is optional
        if (!value) {
            clearState(email);
            return true;
        }

        const valid = email.validity.valid &&
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

        setFieldState(email, valid, 'Please enter a valid email address.');
        return valid;
    }

    function validateMobile() {
        const value = mobile.value.trim();
        let message = '';

        if (!value) {
            message = 'Mobile number is required.';
        } else if (value.length > 30) {
            message = 'Mobile number cannot exceed 30 characters.';
        } else if (!/^[+]?[\d\s()-]+$/.test(value)) {
            message = 'Enter a valid mobile number.';
        } else if (!/[0-9]/.test(value)) {
            message = 'Mobile number must contain digits.';
        }

        const valid = message === '';
        setFieldState(mobile, valid, message);
        return valid;
    }

    function validatePasswordConfirmation() {
        const p = password.value;
        const c = confirmPassword.value;

        // Editing: both blank = keep current password
        if (editing && p === '' && c === '') {
            clearState(confirmPassword);
            return true;
        }

        if (c === '') {
            if (!editing) {
                setFieldState(confirmPassword, false, 'Please confirm your password.');
                return false;
            }
            clearState(confirmPassword);
            return true;
        }

        const valid = p === c;
        setFieldState(confirmPassword, valid, 'Passwords do not match.');
        return valid;
    }

    function validatePassword() {
        const value = password.value;
        const optionalBlank = editing && value === '';
        let allValid = true;

        passwordRules.forEach(rule => {
            const el = document.getElementById(rule.id);
            if (!el) return;

            const ok = rule.test(value);
            const show = ok && !optionalBlank;

            el.classList.toggle('text-success', show);
            el.classList.toggle('text-danger', !show);

            const icon = el.querySelector('i');
            if (icon) {
                icon.className = show ? 'bi bi-check-circle me-1' : 'bi bi-x-circle me-1';
            }

            if (!ok) allValid = false;
        });

        if (optionalBlank) {
            clearState(password);
            allValid = true;
        } else {
            setFieldState(password, allValid);
        }

        validatePasswordConfirmation();
        return allValid;
    }

    /* ---------------------------------------------------------
     * Step validation
     * ------------------------------------------------------- */
    function validateStep(index, report = true) {
        const fields = steps[index].querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalid = null;

        fields.forEach(field => {
            if (field.disabled) return;

            // Editing: blank password fields are fine
            if (
                editing &&
                (field.id === 'wz-password' || field.id === 'wz-password2') &&
                field.value === ''
            ) {
                clearState(field);
                return;
            }

            if (!field.checkValidity()) {
                field.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalid) firstInvalid = field;
            } else if (!customFields.includes(field)) {
                field.classList.remove('is-invalid');
            }
        });

        // Custom rules for the Account step
        if (index === 0) {
            [
                [email, validateEmail],
                [mobile, validateMobile],
                [password, validatePassword],
                [confirmPassword, validatePasswordConfirmation]
            ].forEach(([field, fn]) => {
                if (!fn()) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = field;
                }
            });
        }

        if (!isValid && report && firstInvalid) {
            firstInvalid.focus();
            if (!firstInvalid.checkValidity()) {
                firstInvalid.reportValidity();
            }
        }

        return isValid;
    }

    /* ---------------------------------------------------------
     * Navigation
     * ------------------------------------------------------- */
    function showStep(index, scroll = true) {
        currentStep = index;

        steps.forEach((step, i) => step.classList.toggle('d-none', i !== index));

        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
            indicator.classList.toggle('completed', i < index);
        });

        prevBtn.disabled = index === 0;

        const isLast = index === steps.length - 1;
        nextBtn.classList.toggle('d-none', isLast);
        submitBtn.classList.toggle('d-none', !isLast);

        if (isLast) renderSummary();

        if (scroll) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /* ---------------------------------------------------------
     * Review summary
     * ------------------------------------------------------- */
    function getFieldLabel(field) {
        const label = field.id
            ? form.querySelector('label[for="' + CSS.escape(field.id) + '"]')
            : null;

        if (label) {
            return label.textContent.replace(/\*/g, '').replace(/\s+/g, ' ').trim();
        }

        return field.name
            .replace(/\[\]/g, '')
            .replace(/\[([^\]]+)\]/g, ' $1')
            .replace(/[_-]/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    }

    function addSummaryItem(label, value, type = 'text') {
        const column = document.createElement('div');
        column.className = 'col-md-6';

        const card = document.createElement('div');
        card.className = 'border rounded p-3 h-100';

        const heading = document.createElement('div');
        heading.className = 'small text-muted mb-1';
        heading.textContent = label;
        card.appendChild(heading);

        if (type === 'image') {
            const img = document.createElement('img');
            img.src = value;
            img.alt = label;
            img.className = 'img-thumbnail mt-1';
            img.style.maxWidth = '180px';
            img.style.maxHeight = '180px';
            card.appendChild(img);
        } else {
            const content = document.createElement('div');
            content.className = 'fw-semibold text-break';
            content.style.whiteSpace = 'pre-wrap';
            content.textContent = value || '—';
            card.appendChild(content);
        }

        column.appendChild(card);
        summary.appendChild(column);
    }

    function renderSummary() {
        summary.replaceChildren();

        blobUrls.forEach(url => URL.revokeObjectURL(url));
        blobUrls = [];

        const fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            if (!field.name || field.disabled) return;
            if (excludedFields.includes(field.name)) return;

            if (['hidden', 'submit', 'button', 'reset'].includes(field.type)) return;

            if ((field.type === 'radio' || field.type === 'checkbox') && !field.checked) return;

            const label = getFieldLabel(field);

            // ----- File inputs -----
            if (field.type === 'file') {
                const file = field.files && field.files[0];

                if (file) {
                    if (file.type.startsWith('image/')) {
                        const url = URL.createObjectURL(file);
                        blobUrls.push(url);
                        addSummaryItem(label + ' (new image)', url, 'image');
                    } else {
                        addSummaryItem(label, file.name);
                    }
                    return;
                }

                // Image kept from a previous failed submit
                const retained = Array.from(
                    form.querySelectorAll('img[data-retained-image]')
                ).find(img => img.dataset.retainedImage === field.name);

                if (retained) {
                    addSummaryItem(label + ' (previously uploaded)', retained.src, 'image');
                    return;
                }

                // Existing image from database (edit mode)
                if (editing && existingImages[field.name]) {
                    addSummaryItem(label + ' (current image)', existingImages[field.name], 'image');
                }

                return;
            }

            // ----- Normal fields -----
            let value = '';

            if (field.type === 'checkbox') {
                const roleName = field.closest('label')?.querySelector('strong');
                value = roleName ? roleName.textContent.trim() : (field.value || 'Yes');
            } else if (field.tagName === 'SELECT') {
                if (field.multiple) {
                    value = Array.from(field.selectedOptions)
                        .map(o => o.textContent.trim())
                        .join(', ');
                } else {
                    value = field.selectedOptions[0]
                        ? field.selectedOptions[0].textContent.trim().replace(/\s+/g, ' ')
                        : '';
                }
                // Skip placeholder option
                if (field.value === '') value = '';
            } else {
                value = field.value.trim();
            }

            if (!value) return;

            addSummaryItem(label, value);
        });

        if (summary.children.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-muted';
            empty.textContent = 'No information has been entered yet.';
            summary.appendChild(empty);
        }
    }

    /* ---------------------------------------------------------
     * Events
     * ------------------------------------------------------- */
    nextBtn.addEventListener('click', function () {
        if (!validateStep(currentStep)) return;
        if (currentStep < steps.length - 1) showStep(currentStep + 1);
    });

    prevBtn.addEventListener('click', function () {
        if (currentStep > 0) showStep(currentStep - 1);
    });

    // Live validation
    email.addEventListener('input', validateEmail);
    email.addEventListener('blur', validateEmail);
    mobile.addEventListener('input', validateMobile);
    mobile.addEventListener('blur', validateMobile);
    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePasswordConfirmation);

    // Clear error styling on edit (except fields with live validation)
    function clearInvalid(event) {
        if (customFields.includes(event.target)) return;
        event.target.classList.remove('is-invalid');
    }
    form.addEventListener('input', clearInvalid);
    form.addEventListener('change', clearInvalid);

    // Final submit
    form.addEventListener('submit', function (event) {
        for (let i = 0; i < steps.length; i++) {
            if (!validateStep(i, false)) {
                event.preventDefault();
                showStep(i);
                validateStep(i, true);
                return;
            }
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Submitting...';
    });

    /* ---------------------------------------------------------
     * Lawyer assignment toggle
     * ------------------------------------------------------- */
    const statusSelect = document.getElementById('member_status');
    const wrapper = document.getElementById('lawyer-assignment-wrapper');
    const lawyerSelect = document.getElementById('lawyer_id');

    if (statusSelect && wrapper && lawyerSelect) {
        const lawyerRequiredStatuses = ['lawyer_assistant', 'apprentice'];

        const toggleLawyerField = () => {
            const needsLawyer = lawyerRequiredStatuses.includes(statusSelect.value);

            wrapper.style.display = needsLawyer ? '' : 'none';
            lawyerSelect.disabled = !needsLawyer;
            lawyerSelect.required = needsLawyer;

            if (!needsLawyer) lawyerSelect.value = '';
        };

        statusSelect.addEventListener('change', toggleLawyerField);
        toggleLawyerField();
    }

    // Initialize
    showStep(0, false);
});
</script>