<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TodoController extends Controller
{
    public function index(): View
    {
        $todos = Todo::with('files')->latest()->get();
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

        return redirect()->route('todos.index')->with('success', 'Todo deleted successfully.');
    }

    public function toggle(Request $request, Todo $todo): RedirectResponse
    {
        $todo->update(['completed' => !$todo->completed]);

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
