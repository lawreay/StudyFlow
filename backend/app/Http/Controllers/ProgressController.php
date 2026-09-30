<?php

namespace App\Http\Controllers;

use App\Models\PlayerProgress;
use App\Http\Resources\PlayerProgressResource;
use Illuminate\Http\Request;

class ProgressController extends ApiController
{
    public function index(Request $request)
    {
        $progress = PlayerProgress::where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->success(PlayerProgressResource::collection($progress)->resolve(), 'Progress loaded');
    }
}
