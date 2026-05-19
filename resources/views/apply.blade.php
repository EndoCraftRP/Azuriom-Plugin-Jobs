@extends('layouts.app')

@section('title', $position->name)

@section('content')
    <div class="container content">
        <div class="mb-3">
            <a href="{{ route('jobs.index') }}" class="btn btn-secondary">{{ trans('messages.actions.back') }}</a>
        </div>
        <h1>{{ $position->translatedName() }}</h1>
        <div class="mb-3">{!! $position->translatedDescription() !!}</div>
        @if(!empty($position->keywords))
            <div class="mb-3">
                @foreach($position->keywords as $keyword)
                    <span class="badge bg-secondary">{{ $keyword }}</span>
                @endforeach
            </div>
        @endif

        @if($activeApplication)
            <div class="alert alert-info">
                {{ trans('jobs::messages.already_applied') }}
                <a href="{{ route('jobs.status', $activeApplication) }}">{{ trans('jobs::messages.view_status') }}</a>
            </div>
        @elseif(! $position->isAcceptingApplications())
            <div class="alert alert-warning">{{ trans('jobs::messages.position_closed') }}</div>
        @else
            <form method="POST" action="{{ route('jobs.store', $position) }}">
                @csrf
                <div class="row g-3">
                    @foreach($position->fields as $field)
                        <div class="col-md-{{ in_array($field->col_md, [12, 6, 4], true) ? $field->col_md : 12 }}">
                            <label class="form-label">{{ $field->label }}</label>
                            @if($field->type === 'textarea')
                                <textarea name="field_{{ $field->id }}" class="form-control" rows="5">{{ old('field_'.$field->id) }}</textarea>
                            @elseif($field->type === 'number')
                                <input type="number" name="field_{{ $field->id }}" class="form-control" value="{{ old('field_'.$field->id) }}">
                            @elseif($field->type === 'select')
                                <select name="field_{{ $field->id }}" class="form-select">
                                    <option value="">{{ trans('jobs::messages.select_placeholder') }}</option>
                                    @foreach($field->options ?? [] as $option)
                                        <option value="{{ $option }}" @selected(old('field_'.$field->id) === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif($field->type === 'checkbox')
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="field_{{ $field->id }}" value="1" @checked(old('field_'.$field->id))>
                                </div>
                            @else
                                <input type="text" name="field_{{ $field->id }}" class="form-control" value="{{ old('field_'.$field->id) }}">
                            @endif
                            @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary mt-3">{{ trans('jobs::messages.apply') }}</button>
            </form>
        @endif
    </div>
@endsection
