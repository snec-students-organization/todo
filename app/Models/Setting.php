<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme',
        'daily_streak',
        'last_completed_date',
        'points',
        'badges',
        'email_notifications',
        'browser_notifications'
    ];

    protected $casts = [
        'daily_streak' => 'integer',
        'points' => 'integer',
        'badges' => 'array',
        'email_notifications' => 'boolean',
        'browser_notifications' => 'boolean',
        'last_completed_date' => 'date',
    ];

    /**
     * Get user who owns these settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
