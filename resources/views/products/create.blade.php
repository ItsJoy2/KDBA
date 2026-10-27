@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header">Create Product</div>
        <div class="card-body">
            <form method="POST" action="{{ route('products.store') }}">
                @include('products.partials.__form')
            </form>
        </div>
    </div>
</div>
@endsection