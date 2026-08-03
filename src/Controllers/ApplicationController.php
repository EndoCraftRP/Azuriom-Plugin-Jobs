<?php

namespace Azuriom\Plugin\Jobs\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Jobs\Models\Application;
use Azuriom\Plugin\Jobs\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'reviewing'];

    public function index()
    {
        $positions = Position::withCount(['applications', 'applications as active_applications_count' => function ($query) {
            $query->whereIn('status', self::ACTIVE_STATUSES);
        }])->orderBy('order')->get();

        return view('jobs::index', ['positions' => $positions]);
    }

    public function show(Position $position)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $active = $this->activeApplicationQuery($position)->latest()->first();

        return view('jobs::apply', ['position' => $position->load('fields'), 'activeApplication' => $active]);
    }

    public function store(Request $request, Position $position)
    {
        if (! $position->isAcceptingApplications()) {
            return back()->with('error', trans('jobs::messages.position_closed'));
        }

        $existing = $this->activeApplicationQuery($position)->exists();

        if ($existing) {
            return back()->with('error', trans('jobs::messages.already_applied'));
        }

        $rules = [];
        $attributes = [];
        $fields = $position->fields()->orderBy('order')->get();

        foreach ($fields as $field) {
            if ($field->type === 'html') {
                continue;
            }

            $key = 'field_'.$field->id;
            $attributes[$key] = $field->label;

            if ($field->type === 'date_range') {
                $startKey = $key . '_start';
                $endKey = $key . '_end';
                $rules[$startKey] = $field->is_required ? ['required', 'date'] : ['nullable', 'date'];
                $rules[$endKey] = $field->is_required ? ['required', 'date', 'after_or_equal:'.$startKey] : ['nullable', 'date', 'after_or_equal:'.$startKey];
                $attributes[$startKey] = $field->label . ' (Start)';
                $attributes[$endKey] = $field->label . ' (End)';
                continue;
            }

            if ($field->type === 'attachment') {
                $rules[$key . '_files'] = ['nullable', 'array'];
                $rules[$key . '_files.*'] = ['file'];
                $rules[$key . '_urls'] = ['nullable', 'array'];
                $rules[$key . '_urls.*'] = ['string', 'url'];
                continue;
            }

            $fieldRules = $field->is_required ? ['required'] : ['nullable'];

            if ($field->type === 'number') {
                $fieldRules[] = 'numeric';
                $min = $field->option('min');
                if ($min !== null && $min !== '') {
                    $fieldRules[] = 'min:' . $min;
                }
                $max = $field->option('max');
                if ($max !== null && $max !== '') {
                    $fieldRules[] = 'max:' . $max;
                }
            } elseif ($field->type === 'date') {
                $fieldRules[] = 'date';
            } elseif ($field->type === 'text' || $field->type === 'textarea') {
                $fieldRules[] = 'string';
                $min = $field->option('min');
                if ($min !== null && $min !== '') {
                    $fieldRules[] = 'min:' . $min;
                }
                $max = $field->option('max');
                if ($max !== null && $max !== '') {
                    $fieldRules[] = 'max:' . $max;
                } else {
                    $fieldRules[] = $field->type === 'text' ? 'max:500' : 'max:3000';
                }
                $regex = $field->option('regex');
                if ($regex !== null && $regex !== '') {
                    $fieldRules[] = 'regex:' . $regex;
                }
            } elseif ($field->type === 'select' || $field->type === 'radio') {
                $fieldRules[] = 'string';
                $allowedOptions = $field->options ?? [];
                if ($field->option('allow_other')) {
                    $allowedOptions = array_merge($allowedOptions, ['other']);

                    $otherKey = $key . '_other';
                    $rules[$otherKey] = [
                        'required_if:' . $key . ',other',
                        'nullable',
                        'string',
                        'max:255'
                    ];
                    $attributes[$otherKey] = $field->label . ' (' . trans('jobs::messages.other') . ')';
                }
                if (!empty($allowedOptions)) {
                    $fieldRules[] = 'in:' . implode(',', $allowedOptions);
                }
            } elseif ($field->type === 'checkbox') {
                $fieldRules[] = 'array';
                $allowedOptions = $field->options ?? [];
                if ($field->option('allow_other')) {
                    $allowedOptions = array_merge($allowedOptions, ['other']);

                    $otherKey = $key . '_other';
                    $rules[$otherKey] = [
                        function ($attribute, $value, $fail) use ($request, $key) {
                            $choices = $request->input($key);
                            if (is_array($choices) && in_array('other', $choices) && empty($value)) {
                                $fail(trans('jobs::messages.validation_other_required'));
                            }
                        },
                        'nullable',
                        'string',
                        'max:255'
                    ];
                    $attributes[$otherKey] = $field->label . ' (' . trans('jobs::messages.other') . ')';
                }
                if (!empty($allowedOptions)) {
                    $rules[$key . '.*'] = ['in:' . implode(',', $allowedOptions)];
                }
            } else {
                $fieldRules[] = 'string';
            }

            $rules[$key] = $fieldRules;
        }

        $validator = Validator::make($request->all(), $rules, [], $attributes);

        $validator->after(function ($validator) use ($request, $fields) {
            foreach ($fields as $field) {
                if ($field->type !== 'attachment') {
                    continue;
                }

                $key = 'field_' . $field->id;
                $filesKey = $key . '_files';
                $urlsKey = $key . '_urls';

                $files = $request->file($filesKey) ?? [];
                if (!is_array($files)) {
                    $files = [$files];
                }
                $files = array_filter($files);

                $urls = $request->input($urlsKey) ?? [];
                if (!is_array($urls)) {
                    $urls = [$urls];
                }
                $urls = array_filter(array_map('trim', $urls));

                $totalCount = count($files) + count($urls);

                if ($field->is_required && $totalCount === 0) {
                    $validator->errors()->add($key, trans('validation.required', ['attribute' => $field->label]));
                    continue;
                }

                $allowUrls = (bool) $field->option('allow_urls', true);
                if (!$allowUrls && count($urls) > 0) {
                    $validator->errors()->add($key, trans('jobs::messages.invalid_url'));
                }

                $maxFiles = (int) ($field->option('max_files') ?: 5);
                if ($totalCount > $maxFiles) {
                    $validator->errors()->add($key, trans('jobs::messages.limit_reached', ['max' => $maxFiles]));
                }

                $allowedExtsStr = $field->option('allowed_extensions') ?: 'pdf,jpg,png,jpeg';
                $allowedExts = array_map('trim', explode(',', strtolower($allowedExtsStr)));
                $maxSizeMB = (float) ($field->option('max_size') ?: 5);
                $maxSizeBytes = $maxSizeMB * 1024 * 1024;

                $totalSize = 0;
                foreach ($files as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (!in_array($ext, $allowedExts, true)) {
                        $validator->errors()->add($key, trans('jobs::messages.invalid_extension', ['extensions' => $allowedExtsStr]));
                    }
                    $totalSize += $file->getSize();
                }

                if ($totalSize > $maxSizeBytes) {
                    $validator->errors()->add($key, trans('jobs::messages.size_limit_exceeded', ['max' => $maxSizeMB]));
                }

                $trustedDomainsStr = setting('jobs.trusted_domains');
                if (!empty($trustedDomainsStr)) {
                    $trustedDomains = array_filter(array_map('trim', preg_split('/,|\r\n|\r|\n/', $trustedDomainsStr)));
                    if (!empty($trustedDomains)) {
                        foreach ($urls as $url) {
                            $host = parse_url($url, PHP_URL_HOST);
                            if (!$host) {
                                $validator->errors()->add($key, trans('jobs::messages.invalid_url'));
                                continue;
                            }
                            $host = strtolower($host);
                            $matched = false;
                            foreach ($trustedDomains as $domain) {
                                $domain = strtolower($domain);
                                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                                    $matched = true;
                                    break;
                                }
                            }
                            if (!$matched) {
                                $validator->errors()->add($key, trans('jobs::messages.untrusted_domain') . " ({$host})");
                            }
                        }
                    }
                }
            }
        });

        $validated = $validator->validate();

        $answers = [];
        foreach ($fields as $field) {
            if ($field->type === 'html') {
                continue;
            }

            $key = 'field_'.$field->id;

            if ($field->type === 'date_range') {
                $start = $validated[$key.'_start'] ?? null;
                $end = $validated[$key.'_end'] ?? null;
                $answers[$field->id] = ($start && $end) ? trans('jobs::messages.from_to_format', ['start' => $start, 'end' => $end]) : null;
            } elseif (($field->type === 'select' || $field->type === 'radio') && ($validated[$key] ?? null) === 'other') {
                $otherVal = $validated[$key.'_other'] ?? '';
                $answers[$field->id] = trans('jobs::messages.other_format', ['value' => $otherVal]);
            } elseif ($field->type === 'checkbox') {
                $choices = $validated[$key] ?? [];
                if (!is_array($choices)) {
                    $choices = [];
                }
                $formatted = [];
                foreach ($choices as $choice) {
                    if ($choice === 'other') {
                        $otherVal = $request->input($key.'_other') ?? '';
                        $formatted[] = trans('jobs::messages.other_format', ['value' => $otherVal]);
                    } else {
                        $formatted[] = $choice;
                    }
                }
                $answers[$field->id] = !empty($formatted) ? implode(', ', $formatted) : null;
            } elseif ($field->type === 'attachment') {
                $files = $request->file($key . '_files') ?? [];
                if (!is_array($files)) {
                    $files = [$files];
                }
                $files = array_filter($files);

                $urls = $request->input($key . '_urls') ?? [];
                if (!is_array($urls)) {
                    $urls = [$urls];
                }
                $urls = array_filter(array_map('trim', $urls));

                $attachments = [];

                foreach ($files as $file) {
                    if (!$file->isValid()) {
                        continue;
                    }

                    $originalName = $file->getClientOriginalName();
                    $extension = strtolower($file->getClientOriginalExtension());
                    $secureName = Str::random(40) . '.' . $extension;

                    $dir = storage_path('app/jobs-attachments');
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    $destinationPath = $dir . '/' . $secureName;
                    $compressed = false;

                    if (setting('jobs.compress_images') && in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                        $compressed = $this->compressAndSaveImage($file, $destinationPath);
                        if ($compressed) {
                            $originalName = pathinfo($originalName, PATHINFO_FILENAME) . '.jpeg';
                            $newSecureName = pathinfo($secureName, PATHINFO_FILENAME) . '.jpeg';
                            if ($secureName !== $newSecureName) {
                                rename($destinationPath, $dir . '/' . $newSecureName);
                                $secureName = $newSecureName;
                            }
                        }
                    }

                    if (!$compressed) {
                        $file->move($dir, $secureName);
                    }

                    $savedPath = 'jobs-attachments/' . $secureName;
                    $finalSize = file_exists($dir . '/' . $secureName) ? filesize($dir . '/' . $secureName) : $file->getSize();

                    $attachments[] = [
                        'type' => 'file',
                        'name' => $originalName,
                        'path' => $savedPath,
                        'size' => $finalSize,
                    ];
                }

                foreach ($urls as $url) {
                    $attachments[] = [
                        'type' => 'url',
                        'value' => $url,
                    ];
                }

                $answers[$field->id] = $attachments;
            } else {
                $answers[$field->id] = $validated[$key] ?? null;
            }
        }

        $application = Application::create([
            'position_id' => $position->id,
            'user_id' => Auth::id(),
            'answers' => $answers,
            'status' => 'pending',
        ]);

        $webhook = setting('jobs.discord_webhook_url');
        if (! empty($webhook)) {
            try {
                Http::post($webhook, [
                    'embeds' => [[
                        'title' => trans('jobs::messages.discord.new_application_title', ['position' => $position->translatedName()]),
                        'color' => 16776960,
                        'fields' => [
                            ['name' => trans('jobs::messages.discord.user_field'), 'value' => Auth::user()->name, 'inline' => true],
                            ['name' => trans('jobs::messages.discord.position_field'), 'value' => $position->translatedName(), 'inline' => true],
                        ],
                        'timestamp' => now()->toIso8601String(),
                        'footer' => ['text' => trans('jobs::messages.discord.footer', ['site' => site_name()])],
                    ]],
                ]);
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('jobs.status', $application)->with('success', trans('jobs::messages.application_sent'));
    }

    public function status(Application $application)
    {
        $this->authorizeOwnership($application);

        $application->load('position.fields');

        return view('jobs::status', ['application' => $application]);
    }

    public function cancel(Application $application)
    {
        $this->authorizeOwnership($application);

        if (! $application->isActive()) {
            return back()->with('error', trans('jobs::messages.cancel_not_allowed'));
        }

        $application->delete();

        return redirect()->route('jobs.index')->with('success', trans('jobs::messages.application_cancelled'));
    }

    private function activeApplicationQuery(Position $position)
    {
        return Application::where('position_id', $position->id)
            ->where('user_id', Auth::id())
            ->whereIn('status', self::ACTIVE_STATUSES);
    }

    private function authorizeOwnership(Application $application): void
    {
        if ($application->user_id !== Auth::id()) {
            abort(403);
        }
    }

    public function downloadAttachment(Request $request, Application $application, string $filename)
    {
        if (auth()->id() !== $application->user_id && !$request->user()?->can('jobs.applications')) {
            abort(403);
        }

        $filename = basename($filename);
        $path = storage_path('app/jobs-attachments/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function deleteAttachment(Request $request, Application $application, int $fieldId, int $index)
    {
        if (auth()->id() !== $application->user_id && !$request->user()?->can('jobs.applications')) {
            abort(403);
        }

        $answers = $application->answers;

        if (isset($answers[$fieldId]) && is_array($answers[$fieldId])) {
            $attachments = $answers[$fieldId];
            if (isset($attachments[$index])) {
                $item = $attachments[$index];

                if (isset($item['type']) && $item['type'] === 'file' && isset($item['path'])) {
                    $path = storage_path('app/' . $item['path']);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }

                unset($attachments[$index]);
                $attachments = array_values($attachments);

                $answers[$fieldId] = $attachments;

                $application->update(['answers' => $answers]);

                return back()->with('success', trans('jobs::messages.attachment_deleted'));
            }
        }

        return back()->with('error', 'Attachment not found.');
    }

    private function compressAndSaveImage($file, $destinationPath): bool
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $imageInfo = @getimagesize($file->getRealPath());
        if (!$imageInfo) {
            return false;
        }

        $mime = $imageInfo['mime'];
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $img = @imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $img = @imagecreatefrompng($file->getRealPath());
                break;
            case 'image/gif':
                $img = @imagecreatefromgif($file->getRealPath());
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $img = @imagecreatefromwebp($file->getRealPath());
                } else {
                    $img = false;
                }
                break;
            default:
                $img = false;
                break;
        }

        if (!$img) {
            return false;
        }

        $width = imagesx($img);
        $height = imagesy($img);
        $bg = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagecopy($bg, $img, 0, 0, 0, 0, $width, $height);

        $success = imagejpeg($bg, $destinationPath, 65);

        imagedestroy($img);
        imagedestroy($bg);

        return $success;
    }
}
