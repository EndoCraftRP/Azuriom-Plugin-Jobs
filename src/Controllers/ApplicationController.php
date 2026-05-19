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
        $answers = [];
        $fields = $position->fields()->orderBy('order')->get();

        foreach ($fields as $field) {
            $key = 'field_'.$field->id;
            $attributes[$key] = $field->label;
            $fieldRules = $field->is_required ? ['required'] : ['nullable'];
            if ($field->type === 'number') {
                $fieldRules[] = 'numeric';
            } elseif ($field->type === 'checkbox') {
                $fieldRules[] = 'boolean';
            } else {
                $fieldRules[] = 'string';
            }
            if ($field->type === 'text') {
                $fieldRules[] = 'max:500';
            }
            if ($field->type === 'textarea') {
                $fieldRules[] = 'max:3000';
            }
            if ($field->type === 'select' && is_array($field->options)) {
                $fieldRules[] = 'in:'.implode(',', $field->options);
            }
            $rules[$key] = $fieldRules;
        }

        $validated = Validator::make($request->all(), $rules, [], $attributes)->validate();
        foreach ($fields as $field) {
            $answers[$field->id] = $validated['field_'.$field->id] ?? null;
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
