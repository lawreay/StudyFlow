<?php

namespace App\Http\Controllers;

use App\Services\GameProgressSummaryService;
use Illuminate\Http\Request;

class GameProgressController extends ApiController
{
    public function __invoke(Request $request, GameProgressSummaryService $summaryService)
    {
        return $this->success($summaryService->forUser($request->user()), 'Game progress loaded');
    }
}