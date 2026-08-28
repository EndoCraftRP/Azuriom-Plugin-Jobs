<?php

namespace Azuriom\Plugin\Jobs\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
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
        $oldStatus = $application->status;
        $oldAdminNote = $application->admin_note;
        $oldPublicNote = $application->public_note;

        $newStatus = $request->input('status');
        $newAdminNote = $request->input('admin_note');
        $newPublicNote = $request->input('public_note');

        $application->update([
            'status' => $newStatus,
            'admin_note' => $newAdminNote,
            'public_note' => $newPublicNote,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if ($oldStatus !== $newStatus) {
            ActionLog::log('jobs.applications.status', $application);
        } elseif ($oldAdminNote !== $newAdminNote || $oldPublicNote !== $newPublicNote) {
            ActionLog::log('jobs.applications.updated', $application);
        }

        $application->load('position');

        if ($request->boolean('notify')) {
            try {
                $application->user->notify(new ApplicationStatusChanged($application));
            } catch (\Exception $e) {
                return back()->with('error', trans('jobs::messages.email_error', ['error' => $e->getMessage()]));
            }
        }

        return back()->with('success', trans('jobs::messages.status_updated'));
    }

    public function destroy(Application $application)
    {
        $application->delete();

        return redirect()->route('jobs.admin.applications.index')->with('success', trans('jobs::messages.deleted'));
    }
}
