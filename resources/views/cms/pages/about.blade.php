@extends('cms.main')

@section('title', 'About the Association | Khagrachari District Bar Association')
@section('view', 'about')

@section('content')

<!-- ===== PAGE HEADER / BREADCRUMB ===== -->
<header class="page-head">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">About</li>
    </ol></nav>
    <h1>About the Association</h1>
    <p>Guardians of Justice, Integrity, and the Rule of Law in Khagrachari</p>
  </div>
</header>

<!-- ===== HISTORY ===== -->
<section id="history">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-7">
        <h2 class="sec-title">Our history and establishment</h2><div class="rule"></div>
        <p class="lead serif">The Khagrachari District Bar Association began as a small gathering of advocates who believed that justice in the hills should be as accessible as it is anywhere in the country.</p>
        <p>Over the decades it has grown into the professional home of the district’s legal community, defending the independence of the Bar, protecting the dignity of its members and serving citizens of every community, language and background.</p>
        <p>Today the Association supports advocates through training and mentoring, runs legal aid for people who cannot afford counsel, and works with the courts, administration and civil society to keep the rule of law strong in the hill district.</p>
        <ul class="timeline">
          <li><b>1984</b><span>The Bar Association is founded by a group of local advocates.</span></li>
          <li><b>1995</b><span>First dedicated Bar building and library opened to members.</span></li>
          <li><b>2008</b><span>Free legal aid clinic launched for remote hill communities.</span></li>
          <li><b>2024</b><span>Digitalization committee formed to bring records and notices online.</span></li>
        </ul>
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

<!-- ===== MISSION & VISION ===== -->
<section class="alt sec-tight">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6"><div class="vm"><div class="ic"><i class="bi bi-bullseye"></i></div>
        <h3>Our mission</h3>
        <ul><li>Promote ethical, skilled and independent advocacy.</li><li>Provide legal aid to underprivileged citizens.</li><li>Support the professional growth of every member, especially juniors.</li></ul></div></div>
      <div class="col-md-6"><div class="vm"><div class="ic"><i class="bi bi-eye"></i></div>
        <h3>Our vision</h3>
        <ul><li>A modern, transparent and respected Bar Association.</li><li>Justice that reaches every village of the hill district.</li><li>A legal community that leads in service, learning and the rule of law.</li></ul></div></div>
    </div>
  </div>
</section>

<!-- ===== KEY STATISTICS ===== -->
<div class="band">
  <div class="container">
    <div class="row g-4 text-center text-md-start">
      <div class="col-6 col-md-3"><div class="stat"><b>1984</b><span>Year established</span></div></div>
      <div class="col-6 col-md-3"><div class="stat"><b>250+</b><span>Active member advocates</span></div></div>
      <div class="col-6 col-md-3"><div class="stat"><b>12</b><span>Legal aid clinics held</span></div></div>
      <div class="col-6 col-md-3"><div class="stat"><b>1,200+</b><span>Free legal aid cases</span></div></div>
    </div>
  </div>
</div>

<!-- ===== PRESENT EXECUTIVE COMMITTEE ===== -->
<section id="committee">
  <div class="container">
    <h2 class="sec-title">Present Executive Committee</h2><div class="rule"></div>
    <div class="row g-4" id="memberGrid"></div>
  </div>
</section>

@endsection
