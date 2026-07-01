<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Todo extends Model
{
    /** @use HasFactory<\Database\Factories\TodoFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description', 'completed'];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }

    public function files(): HasMany
    {
        return $this->hasMany(TodoFile::class);
    }
}
