@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-4">Add Member</h4>

        @include('admin.users.partials.__form')
    </div>
@endsection