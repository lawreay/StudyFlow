<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'topic_id' => $this->topic_id,
            'xp' => $this->xp,
            'level' => $this->level,
            'completed_questions' => $this->completed_questions,
            'accuracy' => (float) $this->accuracy,
            'score' => $this->score,
        ];
    }
}