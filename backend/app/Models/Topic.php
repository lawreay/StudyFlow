<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'description',
        'lesson_title',
        'lesson_summary',
        'lesson_content',
    ];

    protected function casts(): array
    {
        return ['lesson_content' => 'array'];
    }

    public function hasLesson(): bool
    {
        return $this->lesson_title !== null && $this->lesson_summary !== null && ! empty($this->lesson_content);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
