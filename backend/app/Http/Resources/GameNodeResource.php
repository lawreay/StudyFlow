<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameNodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'topic_id' => $this->topic_id,
            'order' => $this->position,
            'required_xp' => $this->unlock_xp,
            'reward_xp' => $this->reward_xp,
            'required_score' => $this->required_score,
            'is_start_node' => $this->is_start_node,
            'is_unlocked' => (bool) $this->is_unlocked,
            'is_completed' => (bool) $this->is_completed,
            'completed_at' => $this->completed_at,
        ];
    }
}