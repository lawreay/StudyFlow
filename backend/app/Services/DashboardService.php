<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\PlayerProgress;
use App\Models\RewardEvent;
use App\Models\User;
use App\Models\UserAchievement;

class DashboardService
{
    public function __construct(private readonly GameProgressSummaryService $gameProgressSummaryService) {}

    public function forUser(User $user): array
    {
        $completedAttempts = Attempt::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with(['topic', 'questions'])
            ->latest('completed_at')
            ->latest('id')
            ->get();
        $scores = $completedAttempts->map(function (Attempt $attempt): ?float {
            $possibleScore = $attempt->questions->sum(fn ($question) => (int) $question->pivot->points);

            return $possibleScore > 0 ? ($attempt->score / $possibleScore) * 100 : null;
        })->filter(fn ($score) => $score !== null);
        $recentAttempts = $completedAttempts->take(5)->map(fn (Attempt $attempt) => [
            'id' => $attempt->id,
            'topic_id' => $attempt->topic_id,
            'topic_name' => $attempt->topic?->name ?? 'Topic removed',
            'score' => $attempt->score,
            'correct_answers' => $attempt->correct_answers,
            'total_questions' => $attempt->total_questions,
            'duration_seconds' => $attempt->duration_seconds,
            'completed_at' => $attempt->completed_at,
        ])->values()->all();
        $storedProgress = PlayerProgress::where('user_id', $user->id)->first();
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
        $activeAttempt = Attempt::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->with('topic')
            ->latest('id')
            ->first();
        $gameSummary = $this->gameProgressSummaryService->forUser($user);
        $currentWorld = $gameSummary['current_world'];
        $recentRewards = RewardEvent::where('user_id', $user->id)
            ->latest('created_at')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (RewardEvent $event) => [
                'id' => $event->id,
                'type' => $event->type,
                'reference_type' => $event->reference_type,
                'reference_id' => $event->reference_id,
                'xp_amount' => $event->xp_amount,
                'created_at' => $event->created_at,
            ])->all();
        $achievements = UserAchievement::where('user_id', $user->id)
            ->with('achievement')
            ->latest('earned_at')
            ->get()
            ->map(fn (UserAchievement $userAchievement): array => [
                'id' => $userAchievement->achievement->id,
                'name' => $userAchievement->achievement->name,
                'description' => $userAchievement->achievement->description,
                'icon' => $userAchievement->achievement->icon,
                'earned_at' => $userAchievement->earned_at,
            ])->all();
        $averageScore = $scores->isEmpty() ? 0 : round($scores->avg(), 2);

        return [
            'player' => [
                'name' => $user->name,
                'xp' => $progress['xp'],
                'level' => $progress['level'],
                'accuracy' => $progress['accuracy'],
            ],
            'learning' => [
                'completed_attempts' => $completedAttempts->count(),
                'completed_topics' => $completedAttempts->pluck('topic_id')->filter()->unique()->count(),
                'average_score_percent' => $averageScore,
                'recent_attempts' => $recentAttempts,
            ],
            'game' => [
                'world' => $currentWorld['name'] ?? null,
                'current_world' => $currentWorld,
                'completed_nodes' => $gameSummary['completed_nodes'],
                'total_nodes' => $gameSummary['total_nodes'],
                'completion_percentage' => $gameSummary['completion_percentage'],
                'next_nodes' => $gameSummary['next_nodes'],
            ],
            'rewards' => $recentRewards,
            'achievements' => $achievements,
            'progress' => $progress,
            'completed_quizzes' => $completedAttempts->count(),
            'average_score_percent' => $averageScore,
            'active_attempt' => $activeAttempt ? [
                'id' => $activeAttempt->id,
                'topic_id' => $activeAttempt->topic_id,
                'topic_name' => $activeAttempt->topic?->name ?? 'Topic removed',
                'current_question_index' => $activeAttempt->current_question_index,
                'total_questions' => $activeAttempt->total_questions,
                'answered_questions' => $activeAttempt->answers()->count(),
                'elapsed_seconds' => max(
                    (int) $activeAttempt->duration_seconds,
                    (int) ($activeAttempt->started_at?->diffInSeconds(now()) ?? 0),
                ),
            ] : null,
            'recent_activity' => $recentAttempts,
        ];
    }
}
