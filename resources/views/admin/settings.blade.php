@extends('admin.layouts.admin')

@section('title', trans('jobs::messages.admin_settings'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('jobs.admin.settings.update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ trans('jobs::messages.admin.settings.discord_webhook_url') }}</label>
                    <input class="form-control" name="discord_webhook_url" value="{{ old('discord_webhook_url', setting('jobs.discord_webhook_url')) }}">
                </div>
                <button class="btn btn-primary">{{ trans('messages.actions.save') }}</button>
            </form>
        </div>
    </div>
@endsection
