<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttemptResource;
use App\Models\Attempt;
use App\Models\PlayerProgress;
use Illuminate\Http\Request;

class StudentDashboardController extends ApiController
{
    public function __invoke(Request $request)
    {
        $userId = $request->user()->id;
        $completedAttempts = Attempt::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->with(['topic', 'questions'])
            ->latest('completed_at')
            ->get();
        $scores = $completedAttempts->map(function (Attempt $attempt): ?float {
            $possibleScore = $attempt->questions->sum(fn ($question) => (int) $question->pivot->points);

            return $possibleScore > 0 ? ($attempt->score / $possibleScore) * 100 : null;
        })->filter(fn ($score) => $score !== null);
        $activeAttempt = Attempt::where('user_id', $userId)
            ->whereNull('completed_at')
            ->with(['questions.options', 'questions.topic', 'answers.question', 'answers.selectedOption'])
            ->latest('id')
            ->first();
        $storedProgress = PlayerProgress::where('user_id', $userId)->first();
        $progress = $storedProgress ? [
            'xp' => $storedProgress->xp,
            'level' => $storedProgress->level,
            'completed_questions' => $storedProgress->completed_questions,
            'accuracy' => (float) $storedProgress->accuracy,
            'score' => $storedProgress->score,
        ] : [
            'xp' => 0,
            'level' => 1,
            'completed_questions' => 0,
            'accuracy' => 0,
            'score' => 0,
        ];

        return $this->success([
            'progress' => $progress,
            'completed_quizzes' => $completedAttempts->count(),
            'average_score_percent' => $scores->isEmpty() ? 0 : round($scores->avg(), 2),
            'active_attempt' => $activeAttempt ? (new AttemptResource($activeAttempt))->resolve() : null,
            'recent_activity' => $completedAttempts->take(5)->map(fn (Attempt $attempt) => [
                'id' => $attempt->id,
                'topic_name' => $attempt->topic?->name ?? 'Topic removed',
                'score' => $attempt->score,
                'correct_answers' => $attempt->correct_answers,
                'total_questions' => $attempt->total_questions,
                'duration_seconds' => $attempt->duration_seconds,
                'completed_at' => $attempt->completed_at,
            ])->values(),
        ], 'Dashboard loaded');
    }
}