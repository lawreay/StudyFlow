<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'topic_name' => $this->whenLoaded('topic', $this->topic?->name),
            'question_text' => $this->question_text,
            'difficulty' => $this->difficulty,
            'points' => $this->points,
            'explanation' => $this->explanation,
            'options' => $this->whenLoaded('options', fn () => $this->options->map(fn ($option) => [
                'id' => $option->id,
                'option_text' => $option->option_text,
                'is_correct' => (bool) $option->is_correct,
            ])->values()),
        ];
    }
}