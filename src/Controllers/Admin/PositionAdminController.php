<?php

namespace Azuriom\Plugin\Jobs\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Jobs\Models\Field;
use Azuriom\Plugin\Jobs\Models\Position;
use Azuriom\Plugin\Jobs\Requests\PositionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PositionAdminController extends Controller
{
    public function index()
    {
        $positions = Position::withCount('applications')->orderBy('order')->get();

        return view('jobs::admin.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('jobs::admin.positions.create');
    }

    public function store(PositionRequest $request)
    {
        DB::transaction(function () use ($request): void {
            $keywords = $this->parseKeywords($request->input('keywords'));
            $position = Position::create([
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'description' => $request->input('description'),
                'is_open' => $request->boolean('is_open'),
                'max_pending' => $request->input('max_pending'),
                'order' => $request->input('order', 0),
                'published_at' => $request->input('published_at'),
                'closed_at' => $request->input('closed_at'),
                'show_applications_count' => $request->boolean('show_applications_count'),
                'keywords' => $keywords,
            ]);

            $this->syncFields($position, $request->input('fields', []));
        });

        return redirect()->route('jobs.admin.positions.index')->with('success', trans('jobs::messages.position_saved'));
    }

    public function edit(Position $position)
    {
        $position->load('fields');

        return view('jobs::admin.positions.edit', compact('position'));
    }

    public function update(PositionRequest $request, Position $position)
    {
        DB::transaction(function () use ($request, $position): void {
            $keywords = $this->parseKeywords($request->input('keywords'));
            $position->update([
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'description' => $request->input('description'),
                'is_open' => $request->boolean('is_open'),
                'max_pending' => $request->input('max_pending'),
                'order' => $request->input('order', 0),
                'published_at' => $request->input('published_at'),
                'closed_at' => $request->input('closed_at'),
                'show_applications_count' => $request->boolean('show_applications_count'),
                'keywords' => $keywords,
            ]);

            $this->syncFields($position, $request->input('fields', []));
        });

        return back()->with('success', trans('jobs::messages.position_saved'));
    }

    public function destroy(Position $position)
    {
        if ($position->applications()->exists()) {
            return back()->with('error', trans('jobs::messages.position_has_applications'));
        }

        $position->delete();

        return back()->with('success', trans('messages.actions.deleted'));
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('positions', []) as $item) {
            Position::whereKey($item['id'] ?? null)->update(['order' => $item['order'] ?? 0]);
        }

        return back()->with('success', trans('messages.status.updated'));
    }

    protected function syncFields(Position $position, array $fields): void
    {
        $keptIds = [];

        foreach ($fields as $index => $field) {
            $payload = [
                'label' => $field['label'] ?? '',
                'type' => $field['type'] ?? 'text',
                'options' => isset($field['options']) ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $field['options'])))) : null,
                'is_required' => ! empty($field['is_required']),
                'col_md' => in_array((int) ($field['col_md'] ?? 12), [12, 6, 4], true) ? (int) $field['col_md'] : 12,
                'order' => $index,
            ];

            if (! empty($field['id'])) {
                $model = Field::where('position_id', $position->id)->whereKey($field['id'])->first();
                if ($model !== null) {
                    $model->update($payload);
                    $keptIds[] = $model->id;
                }
                continue;
            }

            $newField = $position->fields()->create($payload);
            $keptIds[] = $newField->id;
        }

        $position->fields()->whereNotIn('id', $keptIds)->delete();
    }

    protected function parseKeywords(?string $keywords): array
    {
        if ($keywords === null || trim($keywords) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/,/', $keywords))));
    }
}
