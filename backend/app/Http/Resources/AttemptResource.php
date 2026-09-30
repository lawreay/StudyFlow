<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'topic_id' => $this->topic_id,
            'score' => $this->score,
            'correct_answers' => $this->correct_answers,
            'total_questions' => $this->total_questions,
            'answered_questions' => $this->whenLoaded('answers', fn () => $this->answers->count()),
            'is_completed' => $this->completed_at !== null,
            'completed_at' => $this->completed_at,
            'xp_earned' => $this->whenLoaded('answers', fn () => $this->answers->where('is_correct', true)->count() * 10),
            'answers' => $this->whenLoaded('answers', fn () => $this->answers->map(fn ($answer) => [
                'question_id' => $answer->question_id,
                'question_text' => $answer->question?->question_text,
                'selected_option_id' => $answer->selected_option_id,
                'selected_option_text' => $answer->selectedOption?->option_text,
                'is_correct' => $answer->is_correct,
                'marks_earned' => $answer->marks_earned,
            ])->values()),
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}