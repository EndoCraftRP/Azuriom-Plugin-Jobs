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
            <form method="POST" action="{{ route('jobs.store', $position) }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    @foreach($position->fields as $field)
                        <div class="col-md-{{ in_array($field->col_md, [12, 6, 4], true) ? $field->col_md : 12 }}">
                            @if($field->type === 'html')
                                {!! $field->option('html', '') !!}
                            @else
                                <label class="form-label">
                                    {{ $field->label }}
                                    @if($field->is_required) <span class="text-danger">*</span> @endif
                                </label>

                                @if($field->type === 'textarea')
                                    <textarea name="field_{{ $field->id }}" class="form-control" rows="5">{{ old('field_'.$field->id) }}</textarea>
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                @elseif($field->type === 'number')
                                    <input type="number" name="field_{{ $field->id }}" class="form-control" value="{{ old('field_'.$field->id) }}">
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                @elseif($field->type === 'date')
                                    <input type="date" name="field_{{ $field->id }}" class="form-control" value="{{ old('field_'.$field->id) }}">
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                @elseif($field->type === 'date_range')
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small text-muted">{{ trans('jobs::messages.start_date') }}</label>
                                            <input type="date" name="field_{{ $field->id }}_start" class="form-control" value="{{ old('field_'.$field->id.'_start') }}">
                                            @error('field_'.$field->id.'_start')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">{{ trans('jobs::messages.end_date') }}</label>
                                            <input type="date" name="field_{{ $field->id }}_end" class="form-control" value="{{ old('field_'.$field->id.'_end') }}">
                                            @error('field_'.$field->id.'_end')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                @elseif($field->type === 'select')
                                    <select name="field_{{ $field->id }}" class="form-select select-field">
                                        <option value="">{{ trans('jobs::messages.select_placeholder') }}</option>
                                        @foreach($field->options ?? [] as $option)
                                            <option value="{{ $option }}" @selected(old('field_'.$field->id) === $option)>{{ $option }}</option>
                                        @endforeach
                                        @if($field->option('allow_other'))
                                            <option value="other" @selected(old('field_'.$field->id) === 'other')>{{ trans('jobs::messages.other') }}</option>
                                        @endif
                                    </select>
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror

                                    @if($field->option('allow_other'))
                                        <div class="mt-2 d-none other-input-wrap">
                                            <input type="text" name="field_{{ $field->id }}_other" class="form-control" placeholder="{{ trans('jobs::messages.other_placeholder') }}" value="{{ old('field_'.$field->id.'_other') }}">
                                            @error('field_'.$field->id.'_other')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    @endif
                                @elseif($field->type === 'checkbox')
                                    @foreach($field->options ?? [] as $option)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="field_{{ $field->id }}[]" value="{{ $option }}" @checked(is_array(old('field_'.$field->id)) && in_array($option, old('field_'.$field->id))) id="field_{{ $field->id }}_{{ $loop->index }}">
                                            <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">{{ $option }}</label>
                                        </div>
                                    @endforeach
                                    @if($field->option('allow_other'))
                                        <div class="form-check">
                                            <input class="form-check-input other-checkbox" type="checkbox" name="field_{{ $field->id }}[]" value="other" @checked(is_array(old('field_'.$field->id)) && in_array('other', old('field_'.$field->id))) id="field_{{ $field->id }}_other_checkbox">
                                            <label class="form-check-label" for="field_{{ $field->id }}_other_checkbox">{{ trans('jobs::messages.other') }}</label>
                                        </div>
                                        <div class="mt-2 d-none other-input-wrap">
                                            <input type="text" name="field_{{ $field->id }}_other" class="form-control" placeholder="{{ trans('jobs::messages.other_placeholder') }}" value="{{ old('field_'.$field->id.'_other') }}">
                                            @error('field_'.$field->id.'_other')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    @endif
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                @elseif($field->type === 'radio')
                                    @foreach($field->options ?? [] as $option)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="field_{{ $field->id }}" value="{{ $option }}" @checked(old('field_'.$field->id) === $option) id="field_{{ $field->id }}_{{ $loop->index }}">
                                            <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">{{ $option }}</label>
                                        </div>
                                    @endforeach
                                    @if($field->option('allow_other'))
                                        <div class="form-check">
                                            <input class="form-check-input other-radio" type="radio" name="field_{{ $field->id }}" value="other" @checked(old('field_'.$field->id) === 'other') id="field_{{ $field->id }}_other_radio">
                                            <label class="form-check-label" for="field_{{ $field->id }}_other_radio">{{ trans('jobs::messages.other') }}</label>
                                        </div>
                                        <div class="mt-2 d-none other-input-wrap">
                                            <input type="text" name="field_{{ $field->id }}_other" class="form-control" placeholder="{{ trans('jobs::messages.other_placeholder') }}" value="{{ old('field_'.$field->id.'_other') }}">
                                            @error('field_'.$field->id.'_other')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    @endif
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                @elseif($field->type === 'attachment')
                                    <div class="attachment-field-container border rounded p-3 bg-light"
                                         id="attachment_container_{{ $field->id }}"
                                         data-field-id="{{ $field->id }}"
                                         data-max-files="{{ $field->option('max_files') ?: 5 }}"
                                         data-max-size="{{ $field->option('max_size') ?: 5 }}"
                                         data-allowed-extensions="{{ $field->option('allowed_extensions') ?: 'pdf,jpg,png,jpeg' }}"
                                         data-allow-urls="{{ $field->option('allow_urls', true) ? 'true' : 'false' }}"
                                         data-msg-limit-reached="{{ trans('jobs::messages.limit_reached') }}"
                                         data-msg-size-exceeded="{{ trans('jobs::messages.size_limit_exceeded') }}"
                                         data-msg-invalid-extension="{{ trans('jobs::messages.invalid_extension') }}"
                                         data-msg-invalid-url="{{ trans('jobs::messages.invalid_url') }}">

                                        <!-- Hidden Inputs Container -->
                                        <div class="hidden-inputs-container d-none"></div>

                                        <!-- List of attached files and URLs -->
                                        <div class="attachments-list list-group mb-3 d-none">
                                            <!-- Items will be appended here dynamically by JS -->
                                        </div>

                                        <!-- Limit info -->
                                        <div class="mb-3 text-muted small">
                                            {{ trans('jobs::messages.attachment_limits', [
                                                'max_files' => $field->option('max_files') ?: 5,
                                                'max_size' => $field->option('max_size') ?: 5,
                                                'extensions' => $field->option('allowed_extensions') ?: 'pdf,jpg,png,jpeg'
                                            ]) }}
                                        </div>

                                        <!-- Upload / Add controls -->
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary upload-btn">
                                                <i class="bi bi-file-earmark-arrow-up"></i> {{ trans('jobs::messages.upload_file') }}
                                            </button>
                                            @if($field->option('allow_urls', true))
                                                <button type="button" class="btn btn-sm btn-outline-secondary add-url-btn">
                                                    <i class="bi bi-link-45deg"></i> {{ trans('jobs::messages.add_url') }}
                                                </button>
                                            @endif
                                        </div>

                                        <!-- URL Input box (initially hidden) -->
                                        <div class="url-input-box mt-3 d-none border rounded p-2 bg-white">
                                            <label class="form-label small font-weight-bold">{{ trans('jobs::messages.enter_url') }}</label>
                                            <div class="input-group input-group-sm">
                                                <input type="url" class="form-control url-input" placeholder="https://example.com/image.jpg or https://youtube.com/watch?...">
                                                <button class="btn btn-primary add-url-submit-btn" type="button">{{ trans('messages.actions.add') }}</button>
                                                <button class="btn btn-outline-danger add-url-cancel-btn" type="button">{{ trans('messages.actions.cancel') }}</button>
                                            </div>
                                            <div class="url-error text-danger small mt-1 d-none"></div>
                                        </div>

                                        @error('field_'.$field->id)
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <div class="js-validation-error text-danger mt-1 d-none"></div>
                                    </div>
                                @else
                                    <input type="text" name="field_{{ $field->id }}" class="form-control" value="{{ old('field_'.$field->id) }}">
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary mt-3">{{ trans('jobs::messages.apply') }}</button>
            </form>

            <script src="{{ plugin_asset('jobs', 'js/apply.js') }}" defer></script>
        @endif
    </div>
@endsection
