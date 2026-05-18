<?php

namespace Azuriom\Plugin\Jobs\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Jobs\Models\Application;
use Azuriom\Plugin\Jobs\Models\Position;
use Azuriom\Plugin\Jobs\Notifications\ApplicationStatusChanged;
use Azuriom\Plugin\Jobs\Requests\ApplicationStatusRequest;
use Illuminate\Http\Request;

class ApplicationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::with(['user', 'position']);

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->integer('position_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$term.'%'));
        }

        return view('jobs::admin.index', [
            'applications' => $query->orderByDesc('created_at')->paginate(15)->withQueryString(),
            'positions' => Position::orderBy('order')->get(),
        ]);
    }

    public function show(Application $application)
    {
        $application->load(['user', 'position.fields', 'reviewer']);

        return view('jobs::admin.show', ['application' => $application]);
    }

    public function updateStatus(ApplicationStatusRequest $request, Application $application)
    {
        $application->update([
            'status' => $request->input('status'),
            'admin_note' => $request->input('admin_note'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $application->load('position');
        $application->user->notify(new ApplicationStatusChanged($application));

        return back()->with('success', trans('jobs::messages.status_updated'));
    }

    public function destroy(Application $application)
    {
        $application->delete();

        return redirect()->route('jobs.admin.applications.index')->with('success', trans('jobs::messages.deleted'));
    }
}
