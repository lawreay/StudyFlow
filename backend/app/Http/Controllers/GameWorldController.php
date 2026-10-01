<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameNodeResource;
use App\Http\Resources\GameWorldResource;
use App\Services\GameProgressService;
use Illuminate\Http\Request;

class GameWorldController extends ApiController
{
    public function index(Request $request, GameProgressService $progressService)
    {
        $worlds = $progressService->worldsFor($request->user());

        return $this->success(GameWorldResource::collection($worlds)->resolve(), 'Game worlds loaded');
    }

    public function completeNode(Request $request, int $node, GameProgressService $progressService)
    {
        $result = $progressService->completeNode($request->user(), $node);
        $node = $result['node'];
        $node->setAttribute('is_unlocked', true);
        $node->setAttribute('is_completed', true);
        $node->setAttribute('completed_at', $result['node_progress']->completed_at);

        return $this->success([
            'node' => (new GameNodeResource($node))->resolve(),
            'progress' => [
                'xp' => $result['player_progress']->xp,
                'level' => $result['player_progress']->level,
            ],
        ], 'Node completed');
    }
}