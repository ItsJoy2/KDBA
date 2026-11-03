@extends('cms.main')

@section('title', 'Home | Khagrachari District Bar Association')
@section('view', 'home')

@section('content')

<!-- ===== HERO SLIDER ===== -->
<header id="home" class="hero carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
  <div class="carousel-indicators">
    <button data-bs-target="#home" data-bs-slide-to="0" class="active" aria-label="Slide 1"></button>
    <button data-bs-target="#home" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button data-bs-target="#home" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item s1 active"><div class="caption"><div class="container">
      <h1>Defending justice for every citizen</h1>
      <p>The Khagrachari District Bar Association unites advocates committed to integrity, independence and public service.</p>
      <a href="#history" class="btn btn-gold me-2">About the Association</a><a href="#notices" class="btn btn-line">Latest notices</a></div></div></div>
    <div class="carousel-item s2"><div class="caption"><div class="container">
      <h1>Learning, ethics and advocacy</h1>
      <p>Seminars, workshops and training that keep our members sharp and our profession respected.</p>
      <a href="{{ route('events') }}" class="btn btn-gold me-2">Upcoming events</a><a href="#articles" class="btn btn-line">Read articles</a></div></div></div>
    <div class="carousel-item s3"><div class="caption"><div class="container">
      <h1>A modern bar for a changing court</h1>
      <p>Our digitalization committee is bringing records, notices and services online for members and the public.</p>
      <a href="#digital" class="btn btn-gold me-2">Meet the team</a><a href="{{ route('contact') }}" class="btn btn-line">Contact us</a></div></div></div>
  </div>

  {{-- Animated emblem: transparent WebM, PNG (APNG) fallback --}}
  <div class="hero-emblem" aria-hidden="true">
    <video autoplay loop muted playsinline preload="auto" width="360" height="360">
      <source src="{{ asset('assets/images/khagrachari_bar_logo_v5.webm') }}" type="video/webm">
      <img src="{{ asset('assets/images/khagrachari_bar_logo_v4.png') }}" alt="" width="360" height="360">
    </video>
  </div>
</header>

<!-- ===== HISTORY & CORE VALUES ===== -->
<section id="history">
  <div class="container">
    <div class="row g-5 align-items-stretch">
      <div class="col-lg-7">
        <h2 class="sec-title">Our history and establishment</h2><div class="rule"></div>
        <p class="lead serif">The Khagrachari District Bar Association began as a small gathering of advocates who believed that justice in the hills should be as accessible as it is anywhere in the country.</p>
        <p>Over the decades it has grown into the professional home of the district’s legal community, defending the independence of the Bar, protecting the dignity of its members and serving citizens of every community, language and background.</p>
        <a href="#leadership-messages" class="btn btn-gold mt-2">Read more <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
      <div class="col-lg-5">
        <div class="about-card">
          <h3>Core values</h3>
          <div class="pillar"><i class="bi bi-shield-check"></i><div><h5>Integrity</h5><p>Honest, ethical advocacy in every case.</p></div></div>
          <div class="pillar"><i class="bi bi-award"></i><div><h5>Independence</h5><p>A free Bar that serves clients without fear or favour.</p></div></div>
          <div class="pillar"><i class="bi bi-people"></i><div><h5>Inclusion</h5><p>Equal access to justice for every community in the hills.</p></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== LEADERSHIP MESSAGES ===== -->
<section id="about" class="alt">
  <div class="container">
    <h2 class="sec-title">Messages from our leadership</h2><div class="rule"></div>
    <div class="row g-4">
      <div class="col-lg-6"><article class="msg">
        <div class="head"><img class="photo" data-avatar="Md. Abdul Karim" alt="Md. Abdul Karim, President">
          <div><h3 class="h3">Md. Abdul Karim</h3><div class="role">President</div></div></div>
        <blockquote>“Our bar stands on one promise: no one who seeks justice should be turned away. We will keep strengthening our members, our ethics and our service to the people of this district.”</blockquote>
      </article></div>
      <div class="col-lg-6"><article class="msg">
        <div class="head"><img class="photo" data-avatar="Shahidul Islam" alt="Shahidul Islam, General Secretary">
          <div><h3 class="h3">Shahidul Islam</h3><div class="role">General Secretary</div></div></div>
        <blockquote>“Transparency and teamwork drive everything we do. From training to digital services, our focus is to make the Association open, accountable and useful to every advocate.”</blockquote>
      </article></div>
    </div>
  </div>
</section>

<!-- ===== NOTICE BOARD ===== -->
<section id="notices">
  <div class="container">
    <h2 class="sec-title">Notice board</h2><div class="rule"></div>
    <div class="row gx-4" id="noticeList"></div>
    <div class="text-center mt-4"><a href="{{ route('notices') }}" class="btn btn-outline-gold">View all notices <i class="bi bi-arrow-right ms-1"></i></a></div>
  </div>
</section>

<!-- PDF preview modal -->
<div class="modal fade" id="pdfModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title" id="pdfTitle"></h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body p-0"><iframe id="pdfFrame" class="pdf-frame" title="PDF preview"></iframe></div>
  <div class="modal-footer"><a id="pdfDl" class="btn btn-gold" download><i class="bi bi-download me-2"></i>Download PDF</a></div>
</div></div></div>

<!-- ===== EVENTS ===== -->
<section id="events" class="alt">
  <div class="container">
    <h2 class="sec-title">Events and seminars</h2><div class="rule"></div>
    <div class="row g-4" id="eventList" data-events-url="{{ route('events') }}"></div>
    <div class="text-center mt-4"><a href="{{ route('events') }}" class="btn btn-outline-gold">View all events <i class="bi bi-arrow-right ms-1"></i></a></div>
  </div>
</section>

<!-- ===== CURRENT COMMITTEE SLIDER ===== -->
<section id="committee">
  <div class="container">
    <h2 class="sec-title">Current executive committee</h2><div class="rule"></div>
    <div class="slider" id="sCommittee"><button class="nav-arrow l" aria-label="Previous"><i class="bi bi-chevron-left"></i></button><div class="track"></div><button class="nav-arrow r" aria-label="Next"><i class="bi bi-chevron-right"></i></button></div>
    <div class="text-center mt-4"><a href="{{ route('committee') }}" class="btn btn-outline-gold">Committee archive <i class="bi bi-arrow-right ms-1"></i></a></div>
  </div>
</section>

<!-- ===== LATEST ARTICLES ===== -->
<section id="articles" class="alt">
  <div class="container">
    <h2 class="sec-title">Latest articles</h2><div class="rule"></div>
    <div class="row g-4" id="articleList"></div>
    <div class="text-center mt-4"><a href="{{ route('articles') }}" class="btn btn-outline-gold">All articles <i class="bi bi-arrow-right ms-1"></i></a></div>
  </div>
</section>

<!-- ===== FOUNDERS ===== -->
<section id="founders">
  <div class="container">
    <h2 class="sec-title">Founders and builders of the Association</h2>
    <p class="sec-sub">We honour the advocates whose vision and sacrifice built this Bar.</p><div class="rule"></div>
    <div class="slider tribute" id="sFounders"><button class="nav-arrow l" aria-label="Previous"><i class="bi bi-chevron-left"></i></button><div class="track"></div><button class="nav-arrow r" aria-label="Next"><i class="bi bi-chevron-right"></i></button></div>
  </div>
</section>

<!-- ===== DIGITALIZATION COMMITTEE ===== -->
<section id="digital" class="sec-flush-top">
  <div class="container">
    <h2 class="sec-title">Digitalization committee</h2>
    <p class="sec-sub">The special committee moving the Association’s records and services online.</p><div class="rule"></div>
    <div class="slider" id="sDigital"><button class="nav-arrow l" aria-label="Previous"><i class="bi bi-chevron-left"></i></button><div class="track"></div><button class="nav-arrow r" aria-label="Next"><i class="bi bi-chevron-right"></i></button></div>
  </div>
</section>

@endsection
