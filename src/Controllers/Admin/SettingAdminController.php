<?php

namespace Azuriom\Plugin\Jobs\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Setting;
use Illuminate\Http\Request;

class SettingAdminController extends Controller
{
    public function edit()
    {
        return view('jobs::admin.settings');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'discord_webhook_url' => ['nullable', 'url', 'max:255'],
        ]);

        Setting::updateSettings([
            'jobs.discord_webhook_url' => $validated['discord_webhook_url'] ?? null,
        ]);

        return back()->with('success', trans('messages.status.updated'));
    }
}
