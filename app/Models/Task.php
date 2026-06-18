<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'due_time',
        'repeat_type',
        'estimated_minutes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'estimated_minutes' => 'integer',
    ];

    /**
     * Get user who owns this task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get category of the task.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }

    /**
     * Scope for overdue tasks (due date before today and not completed/cancelled).
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', Carbon::today())
                     ->whereNotIn('status', ['Completed', 'Cancelled']);
    }

    /**
     * Scope for today's tasks.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->where('due_date', Carbon::today());
    }

    /**
     * Scope for upcoming tasks (due date in future).
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('due_date', '>', Carbon::today());
    }

    /**
     * Scope for filtering tasks based on request criteria.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date'])) {
            $query->where('due_date', $filters['date']);
        }

        return $query;
    }
}
