<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'topic_id' => $this->topic_id,
            'question_text' => $this->question_text,
            'points' => $this->points,
            'topic' => $this->whenLoaded('topic', fn () => [
                'id' => $this->topic->id,
                'name' => $this->topic->name,
            ]),
            'options' => $this->whenLoaded('options', fn () => $this->options->map(fn ($option) => [
                'id' => $option->id,
                'option_text' => $option->option_text,
            ])->values()),
        ];
    }
}