<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TodoFile extends Model
{
    protected $fillable = ['todo_id', 'original_name', 'stored_path', 'size', 'mime_type'];

    public function todo(): BelongsTo
    {
        return $this->belongsTo(Todo::class);
    }

    public function downloadUrl(): string
    {
        return route('todos.files.download', [$this->todo_id, $this]);
    }

    public function deleteFromStorage(): bool
    {
        return Storage::disk('s3')->delete($this->stored_path);
    }
}
