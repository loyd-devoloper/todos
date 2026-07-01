<?php

use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('todos', TodoController::class)->except(['show']);
Route::patch('todos/{todo}/toggle', [TodoController::class, 'toggle'])->name('todos.toggle');
Route::get('todos/{todo}/files/{file}/download', [TodoController::class, 'downloadFile'])->name('todos.files.download');
Route::delete('todos/{todo}/files/{file}', [TodoController::class, 'deleteFile'])->name('todos.files.destroy');
