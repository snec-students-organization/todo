<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity',
        'details'
    ];

    /**
     * Get user who triggered this log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper to write an activity log.
     */
    public static function log(int $userId, string $activity, ?string $details = null): self
    {
        return self::create([
            'user_id' => $userId,
            'activity' => $activity,
            'details' => $details
        ]);
    }
}
