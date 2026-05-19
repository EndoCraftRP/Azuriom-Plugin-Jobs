@extends('admin.layouts.admin')

@section('title', trans('jobs::messages.admin_applications'))

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <select class="form-select" name="position_id">
                        <option value="">{{ trans('jobs::messages.admin.filters.position') }}</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>{{ $position->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">{{ trans('jobs::messages.admin.filters.status') }}</option>
                        @foreach(['pending','reviewing','accepted','refused'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ trans('jobs::messages.status_'.$status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ trans('jobs::messages.admin.filters.username') }}"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100">{{ trans('jobs::messages.admin.filters.submit') }}</button></div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>{{ trans('jobs::messages.admin.table.user') }}</th><th>{{ trans('jobs::messages.admin.table.position') }}</th><th>{{ trans('jobs::messages.admin.table.date') }}</th><th>{{ trans('jobs::messages.admin.table.status') }}</th><th></th></tr></thead>
                <tbody>
                @foreach($applications as $application)
                    <tr>
                        <td>{{ $application->user->name }}</td>
                        <td>{{ $application->position->name }}</td>
                        <td>{{ format_date($application->created_at) }}</td>
                        <td><span class="badge bg-{{ $application->statusColor() }}">{{ $application->statusLabel() }}</span></td>
                        <td><a class="btn btn-sm btn-primary" href="{{ route('jobs.admin.applications.show', $application) }}">{{ trans('jobs::messages.admin.table.view') }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $applications->links() }}</div>
    </div>
@endsection
