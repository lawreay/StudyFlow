<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'topic_id',
        'score',
        'correct_answers',
        'total_questions',
        'completed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'attempt_questions')
            ->withPivot('points')
            ->withTimestamps();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }
}