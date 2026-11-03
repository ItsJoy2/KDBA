@extends('cms.main')

@section('title', 'Article | Khagrachari District Bar Association')
@section('view', 'article')

@section('content')

<!-- ===== BREADCRUMB ===== -->
<div class="crumb-bar"><div class="container">
  <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('articles') }}">Articles</a></li>
    <li class="breadcrumb-item active" aria-current="page" id="crumbTitle">Article</li>
  </ol></nav>
</div></div>

<!-- ===== ARTICLE ===== -->
<section class="sec-top-sm">
  <div class="container"><div class="row"><div class="col-lg-9 col-xl-8 mx-auto" id="articleRoot"></div></div></div>
</section>

<!-- ===== RELATED ARTICLES ===== -->
<section class="alt" id="relatedSec" hidden>
  <div class="container">
    <h2 class="sec-title">Related articles</h2><div class="rule"></div>
    <div class="row g-4" id="relatedGrid"></div>
  </div>
</section>

@endsection
