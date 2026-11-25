@extends('cms.main')

@section('title', 'Executive Committees Archive | Khagrachari District Bar Association')
@section('view', 'committee')

@section('content')

<!-- ===== PAGE HEADER ===== -->
<header class="page-head">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Committee</li>
    </ol></nav>
    <h1>Executive Committees Archive</h1>
    <p>Honoring the leadership across years who guided our Bar Association</p>
  </div>
</header>

<!-- ===== YEAR FILTER ===== -->
<div class="filter-bar">
  <div class="container d-flex align-items-center gap-3">
    <div class="pills" id="pills" role="group" aria-label="Select committee term"></div>
    <div class="dropdown term-dd">
      <button class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-label="Select term"><i class="bi bi-calendar3 me-2"></i><span id="ddLabel"></span></button>
      <ul class="dropdown-menu dropdown-menu-end" id="ddMenu"></ul>
    </div>
  </div>
</div>

<!-- ===== MEMBERS ===== -->
<section id="committee">
  <div class="container">
    <div class="term-head"><h2 class="sec-title mb-0" id="termTitle"></h2><span class="badge-present" id="termBadge" hidden>Present committee</span></div>
    <p class="sec-sub" id="termCount"></p><div class="rule"></div>
    <div class="row g-4" id="memberGrid" aria-live="polite"></div>
  </div>
</section>

@endsection
