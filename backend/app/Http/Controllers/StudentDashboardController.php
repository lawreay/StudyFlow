<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class StudentDashboardController extends ApiController
{
    public function __invoke(Request $request, DashboardService $dashboardService)
    {
        $dashboard = $dashboardService->forUser($request->user());

        return $this->success((new DashboardResource($dashboard))->resolve(), 'Dashboard loaded');
    }
}