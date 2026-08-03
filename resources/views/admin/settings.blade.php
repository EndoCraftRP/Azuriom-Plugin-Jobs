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
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="discord_webhook_full_data" id="discordWebhookFullData" value="1" @checked(old('discord_webhook_full_data', setting('jobs.discord_webhook_full_data')))>
                        <label class="form-check-label" for="discordWebhookFullData">{{ trans('jobs::messages.admin.settings.discord_webhook_full_data') }}</label>
                    </div>
                    <div class="small text-muted">{{ trans('jobs::messages.admin.settings.discord_webhook_full_data_help') }}</div>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="compress_images" id="compressImages" value="1" @checked(old('compress_images', setting('jobs.compress_images')))>
                        <label class="form-check-label" for="compressImages">{{ trans('jobs::messages.admin.settings.compress_images') }}</label>
                    </div>
                    <div class="small text-muted">{{ trans('jobs::messages.admin.settings.compress_images_help') }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ trans('jobs::messages.admin.settings.trusted_domains') }}</label>
                    <textarea class="form-control" name="trusted_domains" rows="3" placeholder="e.g. youtube.com, imgur.com">{{ old('trusted_domains', setting('jobs.trusted_domains')) }}</textarea>
                    <div class="small text-muted">{{ trans('jobs::messages.admin.settings.trusted_domains_help') }}</div>
                </div>
                <button class="btn btn-primary">{{ trans('messages.actions.save') }}</button>
            </form>
        </div>
    </div>
@endsection
