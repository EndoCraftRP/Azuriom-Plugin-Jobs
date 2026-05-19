@extends('layouts.app')

@section('title', trans('jobs::messages.nav_title'))

@section('content')
    <div class="container content">
        <h1 class="mb-4">{{ trans('jobs::messages.nav_title') }}</h1>
        <div class="row g-3">
            @forelse($positions as $position)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">{{ $position->translatedName() }}</h5>
                            @if($position->description)
                                <p class="card-text">{!! \Illuminate\Support\Str::limit(strip_tags($position->translatedDescription()), 150) !!}</p>
                            @endif
                            @if(!empty($position->keywords))
                                <div class="mb-2">
                                    @foreach($position->keywords as $keyword)
                                        <span class="badge bg-secondary">{{ $keyword }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($position->show_applications_count)
                                <div class="small text-muted mb-2">{{ $position->applications_count ?? $position->active_applications_count }} {{ trans('jobs::messages.applications_count_label') }}</div>
                            @endif
                            @if(! $position->is_open)
                                <span class="badge bg-secondary mb-2">{{ trans('jobs::messages.closed_badge') }}</span>
                            @endif
                            <a href="{{ route('jobs.show', $position) }}" class="btn btn-primary d-block mt-auto">{{ trans('jobs::messages.apply') }}</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">{{ trans('jobs::messages.no_positions') }}</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
