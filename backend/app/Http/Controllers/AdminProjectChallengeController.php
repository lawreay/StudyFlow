<?php

namespace App\Http\Controllers;

use App\Models\ProjectChallenge;
use App\Models\ProjectSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProjectChallengeController extends ApiController
{
    public function index()
    {
        $challenges = ProjectChallenge::with('topic')
            ->withCount('submissions')
            ->latest('id')
            ->get();

        return $this->success($challenges->map(
            fn (ProjectChallenge $challenge): array => $this->challengeData($challenge),
        )->all(), 'Project challenges loaded');
    }

    public function show(ProjectChallenge $challenge)
    {
        $challenge->load('topic')->loadCount('submissions');

        return $this->success($this->challengeData($challenge), 'Project challenge loaded');
    }

    public function store(Request $request)
    {
        $validated = $this->validateChallenge($request);
        $challenge = ProjectChallenge::create($validated);
        $challenge->load('topic')->loadCount('submissions');

        return $this->success($this->challengeData($challenge), 'Project challenge created', 201);
    }

    public function update(Request $request, ProjectChallenge $challenge)
    {
        $challenge->update($this->validateChallenge($request));
        $challenge->load('topic')->loadCount('submissions');

        return $this->success($this->challengeData($challenge), 'Project challenge updated');
    }

    public function submissions(ProjectChallenge $challenge)
    {
        $submissions = $challenge->submissions()
            ->with('user')
            ->latest('updated_at')
            ->get()
            ->map(fn (ProjectSubmission $submission): array => $this->submissionData($submission));

        return $this->success($submissions, 'Project submissions loaded');
    }

    public function review(Request $request, ProjectSubmission $submission)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'needs_revision'])],
            'feedback' => ['required', 'string', 'max:5000'],
        ]);

        $submission->update([
            ...$validated,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        $submission->load('user');

        return $this->success($this->submissionData($submission), 'Project submission reviewed');
    }

    private function validateChallenge(Request $request): array
    {
        return $request->validate([
            'topic_id' => ['nullable', 'integer', 'exists:topics,id'],
            'title' => ['required', 'string', 'max:255'],
            'brief' => ['required', 'string', 'max:5000'],
            'requirements' => ['required', 'array', 'min:1', 'max:20'],
            'requirements.*' => ['required', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);
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
            'is_active' => $challenge->is_active,
            'submissions_count' => $challenge->submissions_count,
        ];
    }

    private function submissionData(ProjectSubmission $submission): array
    {
        return [
            'id' => $submission->id,
            'learner_name' => $submission->user->name,
            'learner_email' => $submission->user->email,
            'project_url' => $submission->project_url,
            'notes' => $submission->notes,
            'status' => $submission->status,
            'feedback' => $submission->feedback,
            'reviewed_at' => $submission->reviewed_at,
            'updated_at' => $submission->updated_at,
        ];
    }
}
