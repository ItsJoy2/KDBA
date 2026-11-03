@extends('cms.main')

@section('title', 'Legal Articles &amp; Insights | Khagrachari District Bar Association')
@section('view', 'articles')

@section('content')

<!-- ===== PAGE HEADER ===== -->
<header class="page-head">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Articles</li>
    </ol></nav>
    <h1>Legal Articles &amp; Insights</h1>
    <p>Scholarly articles, legal analyses, and commentary written by our esteemed members</p>
  </div>
</header>

<!-- ===== ARTICLES GRID + PAGINATION ===== -->
<section>
  <div class="container">
    <div class="row g-4" id="articleGrid"></div>
    <nav aria-label="Article pages" class="mt-5" id="pager"></nav>
  </div>
</section>

@endsection
