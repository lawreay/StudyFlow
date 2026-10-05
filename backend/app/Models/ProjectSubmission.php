<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_challenge_id',
        'project_url',
        'notes',
        'status',
        'feedback',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProjectChallenge::class, 'project_challenge_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
