<?php

namespace App\Services;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\Attempt;
use App\Models\PlayerNodeProgress;
use App\Models\PlayerProgress;
use App\Models\RewardEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GameProgressService
{
    /** @return Collection<int, GameWorld> */
    public function worldsFor(User $user): Collection
    {
        $worlds = GameWorld::where('is_active', true)->with('nodes')->get();
        $nodeIds = $worlds->flatMap(fn (GameWorld $world) => $world->nodes->pluck('id'));
        $completed = PlayerNodeProgress::where('user_id', $user->id)
            ->whereIn('game_node_id', $nodeIds)
            ->whereNotNull('completed_at')
            ->get()
            ->keyBy('game_node_id');
        $xp = (int) (PlayerProgress::where('user_id', $user->id)->value('xp') ?? 0);

        foreach ($worlds as $world) {
            $previousNodeCompleted = true;

            foreach ($world->nodes as $node) {
                $nodeCompleted = $completed->has($node->id);
                $node->setAttribute(
                    'is_unlocked',
                    $xp >= $node->unlock_xp && ($node->is_start_node || $previousNodeCompleted),
                );
                $node->setAttribute('is_completed', $nodeCompleted);
                $node->setAttribute('completed_at', $completed->get($node->id)?->completed_at);
                $previousNodeCompleted = $nodeCompleted;
            }
        }

        return $worlds;
    }

    public function isNodeUnlocked(User $user, GameNode $node): bool
    {
        return $this->nodeUnlockError($user, $node) === null;
    }

    /** @return array{node: GameNode, node_progress: PlayerNodeProgress, player_progress: PlayerProgress, next_available_node: ?GameNode} */
    public function completeNode(User $user, int $nodeId): array
    {
        return DB::transaction(function () use ($user, $nodeId): array {
            $node = GameNode::with('world')->lockForUpdate()->findOrFail($nodeId);

            $unlockError = $this->nodeUnlockError($user, $node);

            if ($unlockError !== null) {
                throw ValidationException::withMessages($unlockError);
            }

            $this->validateLearningRequirement($user, $node);

            $nodeProgress = PlayerNodeProgress::where('user_id', $user->id)
                ->where('game_node_id', $node->id)
                ->lockForUpdate()
                ->first();

            if ($nodeProgress?->completed_at !== null) {
                throw ValidationException::withMessages([
                    'node' => ['This node has already been completed.'],
                ]);
            }

            $nodeProgress ??= new PlayerNodeProgress([
                'user_id' => $user->id,
                'game_node_id' => $node->id,
            ]);
            $nodeProgress->completed_at = now();
            $nodeProgress->save();

            RewardEvent::create([
                'user_id' => $user->id,
                'type' => 'node_completion',
                'reference_type' => 'game_node',
                'reference_id' => $node->id,
                'xp_amount' => $node->reward_xp,
            ]);

            $playerProgress = PlayerProgress::where('user_id', $user->id)->lockForUpdate()->first();
            $playerProgress ??= new PlayerProgress(['user_id' => $user->id]);
            $playerProgress->awardXp($node->reward_xp);

            $nextAvailableNode = GameNode::where('world_id', $node->world_id)
                ->where('position', '>', $node->position)
                ->orderBy('position')
                ->get()
                ->first(fn (GameNode $candidate) => $this->isNodeUnlocked($user, $candidate)
                    && ! PlayerNodeProgress::where('user_id', $user->id)
                        ->where('game_node_id', $candidate->id)
                        ->whereNotNull('completed_at')
                        ->exists());

            return [
                'node' => $node,
                'node_progress' => $nodeProgress,
                'player_progress' => $playerProgress,
                'next_available_node' => $nextAvailableNode,
            ];
        });
    }

    private function validateLearningRequirement(User $user, GameNode $node): void
    {
        if ($node->topic_id === null) {
            return;
        }

        $attempt = Attempt::where('user_id', $user->id)
            ->where('topic_id', $node->topic_id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->latest('id')
            ->first();

        if ($attempt === null) {
            throw ValidationException::withMessages([
                'learning_activity' => ['Complete the linked topic quiz before completing this node.'],
            ]);
        }

        if ($node->required_score !== null && $attempt->score < $node->required_score) {
            throw ValidationException::withMessages([
                'required_score' => ['The completed topic quiz did not meet the required score.'],
            ]);
        }
    }

    private function nodeUnlockError(User $user, GameNode $node): ?array
    {
        if (! $node->world()->where('is_active', true)->exists()) {
            return ['world' => ['This game world is unavailable.']];
        }

        $xp = (int) (PlayerProgress::where('user_id', $user->id)->value('xp') ?? 0);

        if ($xp < $node->unlock_xp) {
            return ['required_xp' => ['Earn more XP to unlock this node.']];
        }

        if ($node->is_start_node) {
            return null;
        }

        $previousNode = GameNode::where('world_id', $node->world_id)
            ->where('position', '<', $node->position)
            ->orderByDesc('position')
            ->first();

        if ($previousNode === null || ! PlayerNodeProgress::where('user_id', $user->id)
            ->where('game_node_id', $previousNode->id)
            ->whereNotNull('completed_at')
            ->exists()) {
            return ['prerequisite_node' => ['Complete the previous node before continuing.']];
        }

        return null;
    }
}