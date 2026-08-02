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
                                            <label class="form-label small text-muted">Start Date</label>
                                            <input type="date" name="field_{{ $field->id }}_start" class="form-control" value="{{ old('field_'.$field->id.'_start') }}">
                                            @error('field_'.$field->id.'_start')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted">End Date</label>
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
                                            <option value="other" @selected(old('field_'.$field->id) === 'other')>Other</option>
                                        @endif
                                    </select>
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror

                                    @if($field->option('allow_other'))
                                        <div class="mt-2 d-none other-input-wrap">
                                            <input type="text" name="field_{{ $field->id }}_other" class="form-control" placeholder="Please specify..." value="{{ old('field_'.$field->id.'_other') }}">
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
                                            <label class="form-check-label" for="field_{{ $field->id }}_other_checkbox">Other</label>
                                        </div>
                                        <div class="mt-2 d-none other-input-wrap">
                                            <input type="text" name="field_{{ $field->id }}_other" class="form-control" placeholder="Please specify..." value="{{ old('field_'.$field->id.'_other') }}">
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
                                            <label class="form-check-label" for="field_{{ $field->id }}_other_radio">Other</label>
                                        </div>
                                        <div class="mt-2 d-none other-input-wrap">
                                            <input type="text" name="field_{{ $field->id }}_other" class="form-control" placeholder="Please specify..." value="{{ old('field_'.$field->id.'_other') }}">
                                            @error('field_'.$field->id.'_other')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                                        </div>
                                    @endif
                                    @error('field_'.$field->id)<div class="text-danger mt-1">{{ $message }}</div>@enderror
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

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Select elements
                    document.querySelectorAll('select.select-field').forEach(function (select) {
                        function checkSelect() {
                            var wrap = select.parentElement.querySelector('.other-input-wrap');
                            if (wrap) {
                                wrap.classList.toggle('d-none', select.value !== 'other');
                            }
                        }
                        select.addEventListener('change', checkSelect);
                        checkSelect();
                    });

                    // Radio elements
                    document.querySelectorAll('.form-check-input[type="radio"]').forEach(function (radio) {
                        var name = radio.name;
                        function checkRadio() {
                            var checkedRadio = document.querySelector('input[name="' + name + '"]:checked');
                            var wrap = radio.closest('.col-md-12, .col-md-6, .col-md-4').querySelector('.other-input-wrap');
                            if (wrap) {
                                wrap.classList.toggle('d-none', !checkedRadio || checkedRadio.value !== 'other');
                            }
                        }
                        radio.addEventListener('change', function () {
                            checkRadio();
                        });
                        checkRadio();
                    });

                    // Checkbox elements
                    document.querySelectorAll('.form-check-input[type="checkbox"].other-checkbox').forEach(function (checkbox) {
                        function checkCheckbox() {
                            var wrap = checkbox.closest('.col-md-12, .col-md-6, .col-md-4').querySelector('.other-input-wrap');
                            if (wrap) {
                                wrap.classList.toggle('d-none', !checkbox.checked);
                            }
                        }
                        checkbox.addEventListener('change', checkCheckbox);
                        checkCheckbox();
                    });
                });
            </script>
        @endif
    </div>
@endsection
