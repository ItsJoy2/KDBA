@extends('cms.main')

@section('title', 'Official Notice Board | Khagrachari District Bar Association')
@section('view', 'notices')

@section('content')

<!-- ===== PAGE HEADER ===== -->
<header class="page-head">
  <div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Notices</li>
    </ol></nav>
    <h1>Official Notice Board</h1>
    <p>Browse all official announcements, circulars, AGM schedules, and legal notices</p>
  </div>
</header>

<!-- ===== SEARCH BAR ===== -->
<div class="filter-bar">
  <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="input-group search search-wrap">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input id="q" type="search" class="form-control" placeholder="Search notices by keyword" aria-label="Search notices">
    </div>
    <span class="text-muted small" id="count" aria-live="polite"></span>
  </div>
</div>

<!-- ===== NOTICE LIST + PAGINATION ===== -->
<section>
  <div class="container">
    <div class="row"><div class="col-xl-10 mx-auto">
      <div id="noticeList"></div>
      <nav aria-label="Notice pages" class="mt-4" id="pager"></nav>
    </div></div>
  </div>
</section>

<!-- PDF preview modal -->
<div class="modal fade" id="pdfModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title" id="pdfTitle"></h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
  <div class="modal-body p-0"><iframe id="pdfFrame" class="pdf-frame" title="PDF preview"></iframe></div>
  <div class="modal-footer"><a id="pdfDl" class="btn btn-gold" download><i class="bi bi-download me-2"></i>Download PDF</a></div>
</div></div></div>

@endsection
