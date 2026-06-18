<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_value',
        'current_value',
        'deadline',
        'status'
    ];

    protected $casts = [
        'deadline' => 'date',
        'target_value' => 'integer',
        'current_value' => 'integer',
    ];

    /**
     * Get user who owns this goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get goal completion percentage.
     */
    public function percentage(): int
    {
        if ($this->target_value <= 0) {
            return 0;
        }

        $percentage = ($this->current_value / $this->target_value) * 100;
        return min(100, max(0, (int) round($percentage)));
    }
}
