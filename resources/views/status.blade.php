@extends('layouts.app')

@section('title', trans('jobs::messages.nav_title'))

@section('content')
    <div class="container content">
        <h1>{{ trans('jobs::messages.nav_title') }}</h1>
        <p>{{ $application->position->translatedName() }}</p>
        <span class="badge bg-{{ $application->statusColor() }}">
            {{ $application->statusLabel() }}
        </span>
        @if($application->isActive())
            <form action="{{ route('jobs.cancel', $application) }}" method="POST" class="mt-3">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">{{ trans('jobs::messages.cancel_application') }}</button>
            </form>
        @endif

        <hr>
        <h5>{{ trans('jobs::messages.answers_title') }}</h5>
        @foreach($application->position->fields as $field)
            <div class="mb-2">
                <strong>{{ $field->label }}</strong><br>
                <span>{{ data_get($application->answers, $field->id, '-') }}</span>
            </div>
        @endforeach
    </div>
@endsection
