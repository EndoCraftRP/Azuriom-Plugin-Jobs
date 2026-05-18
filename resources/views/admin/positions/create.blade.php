@extends('admin.layouts.admin')

@section('title', trans('jobs::messages.admin_positions'))

@include('admin.elements.editor')

@push('footer-scripts')
    <script src="{{ plugin_asset('jobs', 'js/admin.js') }}"></script>
@endpush

@section('content')
    <form method="POST" action="{{ route('jobs.admin.positions.store') }}" class="card">
        @csrf
        @include('jobs::admin.positions.form', ['position' => null, 'fieldsData' => []])
    </form>
@endsection
