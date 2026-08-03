<?php

namespace Azuriom\Plugin\Jobs\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Jobs\Models\Application;
use Azuriom\Plugin\Jobs\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

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

        $validated = Validator::make($request->all(), $rules, [], $attributes)->validate();

        $answers = [];
        foreach ($fields as $field) {
            if ($field->type === 'html') {
                continue;
            }

            $key = 'field_'.$field->id;

            if ($field->type === 'date_range') {
                $start = $validated[$key.'_start'] ?? null;
                $end = $validated[$key.'_end'] ?? null;
                $answers[$field->id] = ($start && $end) ? "From {$start} to {$end}" : null;
            } elseif (($field->type === 'select' || $field->type === 'radio') && ($validated[$key] ?? null) === 'other') {
                $otherVal = $validated[$key.'_other'] ?? '';
                $answers[$field->id] = 'Other: ' . $otherVal;
            } elseif ($field->type === 'checkbox') {
                $choices = $validated[$key] ?? [];
                if (!is_array($choices)) {
                    $choices = [];
                }
                $formatted = [];
                foreach ($choices as $choice) {
                    if ($choice === 'other') {
                        $otherVal = $request->input($key.'_other') ?? '';
                        $formatted[] = 'Other: ' . $otherVal;
                    } else {
                        $formatted[] = $choice;
                    }
                }
                $answers[$field->id] = !empty($formatted) ? implode(', ', $formatted) : null;
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
}
