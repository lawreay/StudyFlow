<?php

namespace App\Services;

use App\Models\GameWorld;
use App\Models\PlayerProgress;
use App\Models\User;

class GameProgressSummaryService
{
    public function __construct(private readonly GameProgressService $gameProgressService)
    {
    }

    public function forUser(User $user): array
    {
        $worlds = $this->gameProgressService->worldsFor($user);
        $currentWorld = $worlds->first(fn (GameWorld $world) => $world->nodes->contains(
            fn ($node) => ! $node->is_completed,
        )) ?? $worlds->last();
        $nodes = $currentWorld?->nodes ?? collect();
        $totalNodes = $nodes->count();
        $completedNodes = $nodes->where('is_completed', true)->count();
        $completionPercentage = $totalNodes > 0 ? (int) round(($completedNodes / $totalNodes) * 100) : 0;
        $xp = (int) (PlayerProgress::where('user_id', $user->id)->value('xp') ?? 0);

        return [
            'completed_nodes' => $completedNodes,
            'total_nodes' => $totalNodes,
            'completion_percentage' => $completionPercentage,
            'xp' => $xp,
            'current_world' => $currentWorld ? [
                'id' => $currentWorld->id,
                'slug' => $currentWorld->slug,
                'name' => $currentWorld->name,
                'progress' => [
                    'completed_nodes' => $completedNodes,
                    'total_nodes' => $totalNodes,
                    'completion_percentage' => $completionPercentage,
                ],
            ] : null,
            'next_nodes' => $nodes
                ->filter(fn ($node) => $node->is_unlocked && ! $node->is_completed)
                ->map(fn ($node) => [
                    'id' => $node->id,
                    'slug' => $node->slug,
                    'name' => $node->name,
                    'order' => $node->position,
                    'required_xp' => $node->unlock_xp,
                    'reward_xp' => $node->reward_xp,
                ])
                ->values()
                ->all(),
        ];
    }
}