@extends('cms.main')

@section('title', 'Events, Seminars &amp; Workshops | Khagrachari District Bar Association')
@section('view', 'events')

@section('content')

<!-- ===== PAGE HEADER ===== -->
<header class="page-head">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Events</li>
    </ol></nav>
    <h1>Events, Seminars &amp; Workshops</h1>
    <p>Stay updated with our professional development events, legal seminars, and community programs</p>
  </div>
</header>

<!-- ===== FILTER ===== -->
<div class="filter-bar">
  <div class="container"><div class="pills" id="filters" role="group" aria-label="Filter events"></div></div>
</div>

<!-- ===== EVENTS GRID ===== -->
<section>
  <div class="container"><div class="row g-4" id="eventGrid"></div></div>
</section>

<!-- ===== REGISTRATION MODAL ===== -->
<div class="modal fade" id="regModal" tabindex="-1" aria-labelledby="regTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="regTitle">Event registration</h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>

    <form id="regForm" novalidate>
      <div class="modal-body">
        <div class="reg-summary" id="regSummary"></div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label" for="fName">Full name (advocate / participant)</label>
            <input id="fName" class="form-control" required minlength="3" autocomplete="name"><div class="invalid-feedback">Please enter your full name.</div></div>
          <div class="col-md-6"><label class="form-label" for="fId">Bar membership ID / enrolment no.</label>
            <input id="fId" class="form-control" required minlength="3"><div class="invalid-feedback">Please enter your membership or enrolment number.</div></div>
          <div class="col-md-6"><label class="form-label" for="fMobile">Mobile / WhatsApp number</label>
            <input id="fMobile" type="tel" class="form-control" required pattern="^(\+?88)?01[3-9][0-9]{8}$" placeholder="01XXXXXXXXX" autocomplete="tel"><div class="invalid-feedback">Enter a valid Bangladeshi mobile number, for example 01712345678.</div></div>
          <div class="col-md-6"><label class="form-label" for="fEmail">Email address</label>
            <input id="fEmail" type="email" class="form-control" required autocomplete="email"><div class="invalid-feedback">Please enter a valid email address.</div></div>
          <div class="col-12"><label class="form-label" for="fAddr">Chamber / office address</label>
            <textarea id="fAddr" class="form-control" rows="2" required minlength="5"></textarea><div class="invalid-feedback">Please enter your chamber or office address.</div></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-gold" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-gold"><i class="bi bi-check2-circle me-1"></i>Confirm Registration</button>
      </div>
    </form>

    <div id="regDone" class="done" hidden>
      <div class="ok"><i class="bi bi-check-lg"></i></div>
      <h3>Registration received</h3>
      <p class="mb-1">Thank you, <b id="doneName"></b>. You are registered for <b id="doneEvent"></b>.</p>
      <p class="text-muted mb-0" id="doneWhen"></p>
      <div class="ref" id="doneRef"></div>
      <div class="mt-4"><button class="btn btn-gold" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div></div>
</div>

@endsection
