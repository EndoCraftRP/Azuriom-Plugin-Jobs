@extends('admin.layouts.admin')

@section('title', trans('jobs::messages.admin_positions'))

@section('content')
    <a class="btn btn-primary mb-3" href="{{ route('jobs.admin.positions.create') }}">{{ trans('messages.actions.add') }}</a>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>{{ trans('jobs::messages.positions.table.name') }}</th><th>{{ trans('jobs::messages.positions.table.open') }}</th><th>{{ trans('jobs::messages.positions.table.applications') }}</th><th>{{ trans('messages.fields.action') }}</th><th></th></tr></thead>
                <tbody>
                @foreach($positions as $position)
                    <tr>
                        <td>{{ $position->name }}</td>
                        <td>{!! $position->is_open ? '<span class="badge bg-success">'.trans('jobs::messages.positions.table.yes').'</span>' : '<span class="badge bg-secondary">'.trans('jobs::messages.positions.table.no').'</span>' !!}</td>
                        <td>{{ $position->applications_count }}</td>
                        <td>
                            <a class="btn btn-sm btn-primary" href="{{ route('jobs.admin.positions.edit', $position) }}">{{ trans('messages.actions.edit') }}</a>
                            <form method="POST" action="{{ route('jobs.admin.positions.destroy', $position) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" data-confirm="delete">{{ trans('messages.actions.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
