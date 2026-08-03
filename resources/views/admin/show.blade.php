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
                        @if($field->type === 'html')
                            <div class="mb-3">{!! $field->option('html', '') !!}</div>
                        @elseif($field->type === 'attachment')
                            <div class="mb-3">
                                <strong>{{ $field->label }}</strong><br>
                                @php
                                    $attachments = data_get($application->answers, $field->id);
                                @endphp
                                @if(!empty($attachments) && is_array($attachments))
                                    <div class="row g-2 mt-1">
                                        @foreach($attachments as $index => $item)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="card h-100">
                                                    <div class="card-body p-2 d-flex flex-column justify-content-between">
                                                        @if($item['type'] === 'file')
                                                            @php
                                                                $ext = strtolower(pathinfo($item['path'] ?? '', PATHINFO_EXTENSION));
                                                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                                                $downloadUrl = route('jobs.attachments.download', [$application, basename($item['path'])]);
                                                            @endphp
                                                            @if($isImage)
                                                                <div class="text-center mb-2 bg-dark rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                                                    <img src="{{ $downloadUrl }}" class="img-fluid rounded" style="max-height: 120px; object-fit: contain;" alt="{{ $item['name'] }}">
                                                                </div>
                                                            @else
                                                                <div class="text-center mb-2 bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px; font-size: 2.5rem;">
                                                                    <i class="bi bi-file-earmark-text"></i>
                                                                </div>
                                                            @endif
                                                            <div class="text-truncate font-weight-bold" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                                            <div class="small text-muted mb-2">{{ round(($item['size'] ?? 0) / (1024 * 1024), 2) }} MB</div>
                                                            <div class="d-flex gap-1 justify-content-between mt-auto">
                                                                <a href="{{ $downloadUrl }}" class="btn btn-sm btn-primary w-100"><i class="bi bi-download"></i> {{ trans('jobs::messages.download') }}</a>
                                                                @if(auth()->user()?->can('jobs.manage'))
                                                                    <form action="{{ route('jobs.attachments.delete', [$application, $field->id, $index]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ trans('jobs::messages.confirm_delete_attachment') }}');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        @elseif($item['type'] === 'url')
                                                            @php
                                                                $url = $item['value'] ?? '';
                                                                $isYoutube = false;
                                                                $ytEmbedUrl = '';
                                                                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                                                                    $isYoutube = true;
                                                                    $ytEmbedUrl = "https://www.youtube.com/embed/" . $match[1];
                                                                }
                                                                $isImageUrl = preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $url) || preg_match('/imgur\.com/i', $url);
                                                            @endphp
                                                            @if($isYoutube)
                                                                <div class="ratio ratio-16x9 mb-2" style="position: relative; overflow: hidden; padding-top: 56.25%;">
                                                                    <iframe src="{{ $ytEmbedUrl }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                                                                </div>
                                                            @elseif($isImageUrl)
                                                                <div class="text-center mb-2 bg-dark rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                                                    <img src="{{ $url }}" class="img-fluid rounded" style="max-height: 120px; object-fit: contain;" alt="Image URL">
                                                                </div>
                                                            @else
                                                                <div class="text-center mb-2 bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px; font-size: 2.5rem;">
                                                                    <i class="bi bi-link-45deg"></i>
                                                                </div>
                                                            @endif
                                                            <div class="text-truncate font-weight-bold" title="{{ $url }}">{{ $url }}</div>
                                                            <div class="small text-muted mb-2">{{ trans('jobs::messages.web_link') }}</div>
                                                            <div class="d-flex gap-1 justify-content-between mt-auto">
                                                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-box-arrow-up-right"></i> {{ trans('jobs::messages.open_link') }}</a>
                                                                @if(auth()->user()?->can('jobs.manage'))
                                                                    <form action="{{ route('jobs.attachments.delete', [$application, $field->id, $index]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ trans('jobs::messages.confirm_delete_attachment') }}');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        @else
                            <div class="mb-2"><strong>{{ $field->label }}</strong><br>{{ data_get($application->answers, $field->id) ?? '-' }}</div>
                        @endif
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
