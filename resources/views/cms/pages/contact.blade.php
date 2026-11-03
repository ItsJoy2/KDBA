@extends('cms.main')

@section('title', 'Contact Us | Khagrachari District Bar Association')

@section('content')

<!-- ===== PAGE HEADER ===== -->
<header class="page-head">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Contact</li>
    </ol></nav>
    <h1>Contact the Association</h1>
    <p>Find the Association, explore member services, or get help in an emergency.</p>
  </div>
</header>

<!-- ===== CONTACT INFORMATION ===== -->
<section>
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-geo-alt"></i></div>
          <h3>Visit the Association</h3>
          <p class="text-muted">Khagrachari District Bar Association<br>Khagrachari, Bangladesh</p>
          <a class="btn btn-outline-gold mt-2" href="https://www.google.com/maps/search/?api=1&amp;query=Khagrachari+District+Bar+Association" target="_blank" rel="noopener noreferrer">
            View on map <i class="bi bi-box-arrow-up-right ms-1"></i>
          </a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-telephone"></i></div>
          <h3>Emergency assistance</h3>
          <p class="text-muted">For urgent emergencies, contact the National Emergency Service.</p>
          <a class="btn btn-gold mt-2" href="tel:999"><i class="bi bi-telephone-fill me-1"></i>Call 999</a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-people"></i></div>
          <h3>Member services</h3>
          <p class="text-muted">Explore committee information, upcoming programs, and official notices.</p>
          <div class="d-flex flex-wrap gap-2 mt-2">
            <a class="btn btn-outline-gold" href="{{ route('committee') }}">Committee</a>
            <a class="btn btn-outline-gold" href="{{ route('events') }}">Events</a>
            <a class="btn btn-outline-gold" href="{{ route('notices') }}">Notices</a>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- ===== EMERGENCY CONTACTS ===== -->
<section class="alt">
  <div class="container">
    <h2 class="sec-title">Emergency contacts</h2>
    <p class="sec-sub">For urgent emergencies, call the National Emergency Service or contact the relevant local service.</p>
    <div class="rule"></div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-telephone-forward"></i></div>
          <h3>National Emergency</h3>
          <p class="text-muted mb-0">For urgent emergency assistance</p>
          <a class="btn btn-gold mt-3" href="tel:999"><i class="bi bi-telephone-fill me-1"></i>Call 999</a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-fire"></i></div>
          <h3>Fire Service</h3>
          <p class="mb-1"><a href="tel:02337714300">02337-714300</a></p>
          <p class="mb-0"><a href="tel:01901021661">01901-021661</a></p>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-shield"></i></div>
          <h3>Police Station</h3>
          <p class="mb-1"><a href="tel:01853131209">01853-131209</a></p>
          <p class="mb-1"><a href="tel:02333343655">02333-343655</a></p>
          <p class="mb-1"><a href="tel:01320109900">01320-109900</a></p>
          <p class="mb-1"><a href="tel:0233334361897">02333-34361897</a></p>
          <p class="mb-2"><a href="tel:01320109903">01320-109903</a></p>
          <a href="https://police.khagrachhari.gov.bd/pages/officers" target="_blank" rel="noopener noreferrer">
            More <i class="bi bi-box-arrow-up-right ms-1"></i>
          </a>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-hospital"></i></div>
          <h3>Sadar Hospital</h3>
          <p class="mb-1"><a href="tel:037161523">0371-61523</a></p>
          <p class="mb-0"><a href="tel:+8802333343015">+8802333343015</a></p>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-bank"></i></div>
          <h3>Bar Association Office</h3>
          <p class="text-muted mb-0">Office number to be verified before publication.</p>
        </article>
      </div>
      <div class="col-md-6 col-lg-4">
        <article class="vm">
          <div class="ic"><i class="bi bi-ambulance"></i></div>
          <h3>Ambulance</h3>
          <p class="mb-1"><a href="tel:01568735254">01568-735254</a></p>
          <p class="mb-0"><a href="tel:01556542552">01556-542552</a></p>
        </article>
      </div>
    </div>
  </div>
</section>

@endsection
