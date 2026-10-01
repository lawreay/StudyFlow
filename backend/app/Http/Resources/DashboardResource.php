<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'player' => $this->resource['player'],
            'learning' => $this->resource['learning'],
            'game' => $this->resource['game'],
            'rewards' => $this->resource['rewards'],
            'progress' => $this->resource['progress'],
            'completed_quizzes' => $this->resource['completed_quizzes'],
            'average_score_percent' => $this->resource['average_score_percent'],
            'active_attempt' => $this->resource['active_attempt'],
            'recent_activity' => $this->resource['recent_activity'],
        ];
    }
}