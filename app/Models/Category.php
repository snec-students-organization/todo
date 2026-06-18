<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'icon'
    ];

    /**
     * Get tasks under this category.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Scope to get global categories (created by admin, user_id is null).
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope to get categories for a specific user (either global or owned by user).
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereNull('user_id')
                     ->orWhere('user_id', $userId);
    }
}
