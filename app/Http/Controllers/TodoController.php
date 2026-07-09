<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TodoController extends Controller
{
    public function index(): View
    {
        $all = Cache::rememberForever('todos.all', fn () =>
            Todo::with('files')->latest()->get()->map(fn ($todo) => [
                'id' => $todo->id,
                'title' => $todo->title,
                'description' => $todo->description,
                'completed' => $todo->completed,
                'created_at' => (string) $todo->created_at,
                'files' => $todo->files->map(fn ($f) => [
                    'id' => $f->id,
                    'original_name' => $f->original_name,
                    'download_url' => $f->downloadUrl(),
                ])->values()->all(),
            ])->all()
        );

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10000;
        $items = collect(array_slice($all, ($page - 1) * $perPage, $perPage));
        $todos = new LengthAwarePaginator($items, count($all), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return view('todos.index', compact('todos'));
    }

    public function create(): View
    {
        return view('todos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        $todo = Todo::create($validated);

        Cache::forget('todos.all');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $this->uploadFile($todo, $file);
            }
        }

        return redirect()->route('todos.index')->with('success', 'Todo created successfully.');
    }

    public function edit(Todo $todo): View
    {
        $todo->load('files');
        return view('todos.edit', compact('todo'));
    }

    public function update(Request $request, Todo $todo): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        $todo->update($validated);

        Cache::forget('todos.all');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $this->uploadFile($todo, $file);
            }
        }

        return redirect()->route('todos.index')->with('success', 'Todo updated successfully.');
    }

    public function destroy(Todo $todo): RedirectResponse
    {
        foreach ($todo->files as $file) {
            $file->deleteFromStorage();
            $file->delete();
        }

        $todo->delete();

        Cache::forget('todos.all');

        return redirect()->route('todos.index')->with('success', 'Todo deleted successfully.');
    }

    public function toggle(Request $request, Todo $todo): RedirectResponse
    {
        $todo->update(['completed' => !$todo->completed]);

        Cache::forget('todos.all');

        return redirect()->route('todos.index')->with('success', 'Todo status updated.');
    }

    public function downloadFile(Todo $todo, TodoFile $file): StreamedResponse
    {
        if (!Storage::disk('s3')->exists($file->stored_path)) {
            abort(404);
        }

        return Storage::disk('s3')->download($file->stored_path, $file->original_name);
    }

    public function deleteFile(Todo $todo, TodoFile $file): RedirectResponse
    {
        $file->deleteFromStorage();
        $file->delete();

        return redirect()->route('todos.edit', $todo)->with('success', 'File deleted successfully.');
    }

    private function uploadFile(Todo $todo, $file): void
    {
        $path = $file->store('todo_files', 's3');

        $todo->files()->create([
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }
}
