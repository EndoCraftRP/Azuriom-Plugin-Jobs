@extends('layouts.app')

@section('title', trans('jobs::messages.nav_title'))

@section('content')
    <div class="container content">
        <h1>{{ trans('jobs::messages.nav_title') }}</h1>
        <p>{{ $application->position->translatedName() }}</p>
        <span class="badge bg-{{ $application->statusColor() }}">
            {{ $application->statusLabel() }}
        </span>
        @if($application->isActive())
            <form action="{{ route('jobs.cancel', $application) }}" method="POST" class="mt-3">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">{{ trans('jobs::messages.cancel_application') }}</button>
            </form>
        @endif

        <hr>
        <h5>{{ trans('jobs::messages.answers_title') }}</h5>
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
                                                    @if(auth()->id() === $application->user_id || auth()->user()?->can('jobs.manage'))
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
                                                    <div class="ratio ratio-16x9 mb-2" style="height: 120px;">
                                                        <iframe src="{{ $ytEmbedUrl }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
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
                                                    @if(auth()->id() === $application->user_id || auth()->user()?->can('jobs.manage'))
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
                <div class="mb-2">
                    <strong>{{ $field->label }}</strong><br>
                    <span>{{ data_get($application->answers, $field->id) ?? '-' }}</span>
                </div>
            @endif
        @endforeach
    </div>
@endsection
