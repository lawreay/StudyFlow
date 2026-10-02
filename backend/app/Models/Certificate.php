<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_world_id',
        'certificate_number',
        'learner_name',
        'program_name',
        'issued_at',
    ];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    public function world(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class, 'game_world_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
