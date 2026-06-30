@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">My Todos</h2>
    </div>

    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($todos as $todo)
            <li class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3 flex-1">
                    <form action="{{ route('todos.toggle', $todo) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="flex-shrink-0">
                            @if ($todo->completed)
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
                            @endif
                        </button>
                    </form>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium {{ $todo->completed ? 'line-through text-gray-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $todo->title }}
                        </p>
                        @if ($todo->description)
                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $todo->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2 ml-4">
                    <a href="{{ route('todos.edit', $todo) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800">Edit</a>
                    <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800">Delete</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="px-6 py-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">No todos yet.</p>
                <a href="{{ route('todos.create') }}" class="mt-2 inline-block text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800">Create your first todo</a>
            </li>
        @endforelse
    </ul>
</div>
@endsection
