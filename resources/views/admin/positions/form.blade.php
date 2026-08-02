<div class="card-body">
    <div class="mb-3">
        <label class="form-label">{{ trans('jobs::messages.positions.form.name') }}</label>
        <input class="form-control" name="name" value="{{ old('name', $position->name ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ trans('jobs::messages.positions.form.description') }}</label>
        <textarea class="form-control html-editor" name="description" rows="6">{{ old('description', $position->description ?? '') }}</textarea>
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">{{ trans('jobs::messages.positions.form.open') }}</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_open" value="1" @checked(old('is_open', $position->is_open ?? true))>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ trans('jobs::messages.positions.form.max_pending') }}</label>
            <input class="form-control" type="number" name="max_pending" value="{{ old('max_pending', $position->max_pending ?? '') }}">
        </div>
        <div class="col-md-5">
            <label class="form-label">{{ trans('jobs::messages.positions.form.keywords') }}</label>
            <input class="form-control" name="keywords" value="{{ old('keywords', implode(', ', $position->keywords ?? [])) }}" placeholder="{{ trans('jobs::messages.positions.form.keywords_placeholder') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ trans('jobs::messages.positions.form.show_applications_count') }}</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="show_applications_count" value="1" @checked(old('show_applications_count', $position->show_applications_count ?? false))>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ trans('jobs::messages.positions.form.published_at') }}</label>
            <input class="form-control" type="datetime-local" name="published_at" value="{{ old('published_at', isset($position?->published_at) ? $position->published_at->format('Y-m-d\\TH:i') : '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ trans('jobs::messages.positions.form.closed_at') }}</label>
            <input class="form-control" type="datetime-local" name="closed_at" value="{{ old('closed_at', isset($position?->closed_at) ? $position->closed_at->format('Y-m-d\\TH:i') : '') }}">
        </div>
    </div>
    <hr>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">{{ trans('jobs::messages.positions.form.fields_title') }}</h5>
        <button class="btn btn-sm btn-outline-primary" type="button" id="add-field">{{ trans('jobs::messages.positions.form.add_field') }}</button>
    </div>
    <div id="fields-builder"></div>
    <template id="field-template">
        <div class="card mb-2 field-item">
            <div class="card-body">
                <input type="hidden" data-name="id">
                <div class="mb-2">
                    <label class="form-label">Field Label (Not shown for HTML blocks)</label>
                    <input class="form-control" placeholder="{{ trans('jobs::messages.positions.form.field_label_placeholder') }}" data-name="label">
                </div>
                <div class="mb-2">
                    <label class="form-label">Field Type</label>
                    <select class="form-select field-type" data-name="type">
                        <option value="text">text</option>
                        <option value="textarea">textarea</option>
                        <option value="number">number</option>
                        <option value="select">Dropdown list</option>
                        <option value="checkbox">Checkbox-like list</option>
                        <option value="radio">Radio button list</option>
                        <option value="date">Single Date</option>
                        <option value="date_range">Date Range</option>
                        <option value="html">Free-Text/HTML Block</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Column Width</label>
                    <select class="form-select" data-name="col_md">
                        <option value="12">100%</option>
                        <option value="6">50%</option>
                        <option value="4">33%</option>
                    </select>
                </div>

                <!-- Selection Options -->
                <div class="mb-2 options-wrap d-none">
                    <label class="form-label">Options (one per line)</label>
                    <textarea class="form-control" rows="3" placeholder="{{ trans('jobs::messages.positions.form.options_placeholder') }}" data-name="options"></textarea>
                </div>

                <!-- Allow Other Option Toggle -->
                <div class="form-check mb-2 allow-other-wrap d-none">
                    <input class="form-check-input" type="checkbox" data-name="allow_other">
                    <label class="form-check-label">Allow 'Other' option with custom text input</label>
                </div>

                <!-- Validation Restrictions -->
                <div class="row mb-2 validation-wrap d-none">
                    <div class="col-6">
                        <label class="form-label">Min limit (chars or value)</label>
                        <input type="number" class="form-control" data-name="min" placeholder="e.g. 5">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Max limit (chars or value)</label>
                        <input type="number" class="form-control" data-name="max" placeholder="e.g. 100">
                    </div>
                </div>
                <div class="mb-2 regex-wrap d-none">
                    <label class="form-label">Regex Pattern (e.g. /^[a-z]+$/i)</label>
                    <input type="text" class="form-control" data-name="regex" placeholder="/^[a-zA-Z0-9]+$/">
                </div>

                <!-- HTML block editor -->
                <div class="mb-2 html-wrap d-none">
                    <label class="form-label">Custom HTML / Text Content</label>
                    <textarea class="form-control" rows="5" data-name="html" placeholder="<p>Enter your HTML or text content here</p>"></textarea>
                </div>

                <!-- Required Toggle -->
                <div class="form-check mb-2 required-wrap">
                    <input class="form-check-input" type="checkbox" data-name="is_required" checked>
                    <label class="form-check-label">{{ trans('jobs::messages.positions.form.required') }}</label>
                </div>

                <button type="button" class="btn btn-sm btn-outline-danger remove-field">{{ trans('messages.actions.delete') }}</button>
            </div>
        </div>
    </template>
</div>
<div class="card-footer">
    <button class="btn btn-primary">{{ trans('messages.actions.save') }}</button>
</div>
