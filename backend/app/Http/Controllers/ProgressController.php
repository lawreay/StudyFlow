<?php

namespace App\Http\Controllers;

use App\Models\PlayerProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends ApiController
{
    public function index(Request $request)
    {
        $progress = PlayerProgress::where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->success($progress, 'Progress loaded');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'xp' => ['required', 'integer'],
            'level' => ['required', 'integer'],
            'completed_questions' => ['required', 'integer'],
            'score' => ['required', 'integer'],
        ]);

        $progress = PlayerProgress::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'subject_id' => $validated['subject_id'] ?? null,
                'topic_id' => $validated['topic_id'] ?? null,
                'xp' => $validated['xp'],
                'level' => $validated['level'],
                'completed_questions' => $validated['completed_questions'],
                'score' => $validated['score'],
            ]
        );

        return $this->success($progress, 'Progress saved');
    }
}
