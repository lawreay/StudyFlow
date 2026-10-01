<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\TopicLessonCompletion;
use Illuminate\Http\Request;

class SubjectController extends ApiController
{
    public function index(Request $request)
    {
        $subjects = Subject::with(['topics' => fn ($query) => $query->withCount('questions')])->get();

        $this->appendLessonStatus($subjects, $request->user()->id);

        return $this->success($subjects, 'Subjects loaded');
    }

    public function show(Request $request, Subject $subject)
    {
        $subject->load(['topics' => fn ($query) => $query->withCount('questions')]);
        $this->appendLessonStatus(collect([$subject]), $request->user()->id);

        return $this->success($subject, 'Subject loaded');
    }

    private function appendLessonStatus(iterable $subjects, int $userId): void
    {
        $topics = collect($subjects)->flatMap(fn (Subject $subject) => $subject->topics);
        $completedTopicIds = TopicLessonCompletion::where('user_id', $userId)
            ->whereIn('topic_id', $topics->pluck('id'))
            ->pluck('topic_id')
            ->all();

        foreach ($topics as $topic) {
            $topic->setAttribute('has_lesson', $topic->hasLesson());
            $topic->setAttribute('lesson_completed', in_array($topic->id, $completedTopicIds, true));
        }
    }
}
