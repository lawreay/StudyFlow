<?php

namespace App\Http\Controllers;

use App\Models\ProjectChallenge;
use App\Models\ProjectSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectChallengeController extends ApiController
{
    public function index(Request $request)
    {
        $challenges = ProjectChallenge::where('is_active', true)
            ->with('topic')
            ->with(['submissions' => fn ($query) => $query->where('user_id', $request->user()->id)])
            ->latest('id')
            ->get();

        return $this->success($challenges->map(
            fn (ProjectChallenge $challenge): array => $this->challengeData($challenge),
        )->all(), 'Project challenges loaded');
    }

    public function show(Request $request, ProjectChallenge $challenge)
    {
        if (! $challenge->is_active) {
            abort(404);
        }

        $challenge->load('topic')->load(['submissions' => fn ($query) => $query->where('user_id', $request->user()->id)]);

        return $this->success($this->challengeData($challenge), 'Project challenge loaded');
    }

    public function submit(Request $request, ProjectChallenge $challenge)
    {
        if (! $challenge->is_active) {
            abort(404);
        }

        $validated = $request->validate([
            'project_url' => ['required', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $existingSubmission = ProjectSubmission::where('user_id', $request->user()->id)
            ->where('project_challenge_id', $challenge->id)
            ->first();

        if ($existingSubmission?->status === 'approved') {
            throw ValidationException::withMessages([
                'submission' => ['This project has already been approved and cannot be changed.'],
            ]);
        }

        $submission = ProjectSubmission::updateOrCreate(
            ['user_id' => $request->user()->id, 'project_challenge_id' => $challenge->id],
            [
                'project_url' => $validated['project_url'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'submitted',
                'feedback' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ],
        );

        return $this->success($this->submissionData($submission), 'Project submitted', 201);
    }

    private function challengeData(ProjectChallenge $challenge): array
    {
        return [
            'id' => $challenge->id,
            'topic_id' => $challenge->topic_id,
            'topic_name' => $challenge->topic?->name,
            'title' => $challenge->title,
            'brief' => $challenge->brief,
            'requirements' => $challenge->requirements,
            'submission' => $challenge->submissions->first()
                ? $this->submissionData($challenge->submissions->first())
                : null,
        ];
    }

    private function submissionData(ProjectSubmission $submission): array
    {
        return [
            'id' => $submission->id,
            'project_url' => $submission->project_url,
            'notes' => $submission->notes,
            'status' => $submission->status,
            'feedback' => $submission->feedback,
            'reviewed_at' => $submission->reviewed_at,
            'created_at' => $submission->created_at,
        ];
    }
}
