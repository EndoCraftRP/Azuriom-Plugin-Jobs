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
            'discord_webhook_full_data' => ['nullable', 'boolean'],
            'compress_images' => ['nullable', 'boolean'],
            'trusted_domains' => ['nullable', 'string'],
        ]);

        Setting::updateSettings([
            'jobs.discord_webhook_url' => $validated['discord_webhook_url'] ?? null,
            'jobs.discord_webhook_full_data' => $request->has('discord_webhook_full_data'),
            'jobs.compress_images' => $request->has('compress_images'),
            'jobs.trusted_domains' => $validated['trusted_domains'] ?? null,
        ]);

        return back()->with('success', trans('messages.status.updated'));
    }
}
