@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden max-w-lg mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Edit Todo</h2>
    </div>

    <form action="{{ route('todos.update', $todo) }}" method="POST" enctype="multipart/form-data" class="px-6 py-4 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
            <input type="text" name="title" id="title" value="{{ old('title', $todo->title) }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ old('description', $todo->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="files" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attach Files</label>
            <input type="file" name="files[]" id="files" multiple
                   class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 dark:file:bg-blue-900 file:text-blue-700 dark:file:text-blue-200 hover:file:bg-blue-100">
            @error('files.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($todo->files->isNotEmpty())
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attached Files</p>
                <ul class="space-y-2">
                    @foreach ($todo->files as $file)
                        <li class="flex items-center justify-between text-sm">
                            <a href="{{ $file->downloadUrl() }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 truncate">
                                {{ $file->original_name }}
                            </a>
                            <div class="flex items-center space-x-2">
                                <span class="text-gray-500 dark:text-gray-400">{{ round($file->size / 1024) }} KB</span>
                                <form action="{{ route('todos.files.destroy', [$todo, $file]) }}" method="POST" onsubmit="return confirm('Delete this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800">Remove</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('todos.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">Update</button>
        </div>
    </form>
</div>
@endsection
