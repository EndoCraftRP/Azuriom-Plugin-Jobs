@extends('admin.layouts.admin')

@section('title', trans('jobs::messages.admin_positions'))

@include('admin.elements.editor')

@push('footer-scripts')
    @php
        $fieldsData = $position->fields->map(function ($f) {
            return [
                'id' => $f->id,
                'label' => $f->label,
                'type' => $f->type,
                'is_required' => $f->is_required,
                'options' => implode("\n", $f->options ?? []),
                'col_md' => $f->col_md ?? 12,
                'allow_other' => $f->option('allow_other', false),
                'min' => $f->option('min', ''),
                'max' => $f->option('max', ''),
                'regex' => $f->option('regex', ''),
                'html' => $f->option('html', ''),
                'allowed_extensions' => $f->option('allowed_extensions', ''),
                'max_files' => $f->option('max_files', ''),
                'max_size' => $f->option('max_size', ''),
                'allow_urls' => $f->option('allow_urls', true),
            ];
        })->values();
    @endphp
    <script>window.fieldsData = @json($fieldsData);</script>
    <script src="{{ plugin_asset('jobs', 'js/admin.js') }}"></script>
@endpush

@section('content')
    <form method="POST" action="{{ route('jobs.admin.positions.update', $position) }}" class="card">
        @csrf
        @method('PUT')
        @include('jobs::admin.positions.form', ['fieldsData' => $position->fields])
    </form>
@endsection
