<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'topic_id',
        'xp',
        'level',
        'completed_questions',
        'accuracy',
        'score',
    ];

    public function awardXp(int $amount): void
    {
        $this->xp = (int) $this->xp + max(0, $amount);
        $this->updateLevelFromXp();
        $this->save();
    }

    public function updateLevelFromXp(): void
    {
        $this->level = match (true) {
            $this->xp >= 500 => 4,
            $this->xp >= 250 => 3,
            $this->xp >= 100 => 2,
            default => 1,
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
