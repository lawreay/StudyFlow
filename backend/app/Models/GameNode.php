<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'world_id',
        'topic_id',
        'slug',
        'name',
        'description',
        'position',
        'unlock_xp',
        'reward_xp',
        'required_score',
        'is_start_node',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'unlock_xp' => 'integer',
            'reward_xp' => 'integer',
            'required_score' => 'integer',
            'is_start_node' => 'boolean',
        ];
    }

    public function world(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class, 'world_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function playerProgress(): HasMany
    {
        return $this->hasMany(PlayerNodeProgress::class, 'game_node_id');
    }
}