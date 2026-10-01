<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerNodeProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_node_id',
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

    public function gameNode(): BelongsTo
    {
        return $this->belongsTo(GameNode::class, 'game_node_id');
    }
}