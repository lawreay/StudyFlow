<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameWorld extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(GameNode::class, 'world_id')->orderBy('position');
    }
}