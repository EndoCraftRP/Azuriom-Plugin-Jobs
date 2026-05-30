@extends('admin.layouts.admin')

@section('title', trans('jobs::messages.admin_applications'))

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <img src="{{ $application->user->getAvatar() }}" width="64" alt="">
                    <h5 class="mt-2">{{ $application->user->name }}</h5>
                    <div>{{ $application->user->email }}</div>
                    <div>{{ $application->position->name }}</div>
                    <div>{{ format_date($application->created_at) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    @foreach($application->position->fields as $field)
                        <div class="mb-2"><strong>{{ $field->label }}</strong><br>{{ data_get($application->answers, $field->id, '-') }}</div>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('jobs.admin.applications.status', $application) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">{{ trans('messages.fields.status') }}</label>
                            <select class="form-select" name="status">
                                @foreach(['pending','reviewing','accepted','refused'] as $status)
                                    <option value="{{ $status }}" @selected($application->status === $status)>{{ trans('jobs::messages.status_'.$status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ trans('jobs::messages.admin_note') }}</label>
                            <textarea class="form-control" name="admin_note" rows="5">{{ old('admin_note', $application->admin_note) }}</textarea>
                        </div>
                        <button class="btn btn-primary">{{ trans('jobs::messages.update_status') }}</button>
                    </form>
                    <a class="btn btn-danger mt-3" href="{{ route('jobs.admin.applications.destroy', $application) }}" data-confirm="delete">
                        {{ trans('messages.actions.delete') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
