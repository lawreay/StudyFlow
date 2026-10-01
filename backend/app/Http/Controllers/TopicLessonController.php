<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\TopicLessonCompletion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TopicLessonController extends ApiController
{
    public function show(Request $request, Topic $topic)
    {
        $this->ensureLessonExists($topic);

        return $this->success($this->lessonData($request, $topic), 'Lesson loaded');
    }

    public function complete(Request $request, Topic $topic)
    {
        $this->ensureLessonExists($topic);

        TopicLessonCompletion::firstOrCreate(
            ['user_id' => $request->user()->id, 'topic_id' => $topic->id],
            ['completed_at' => now()],
        );

        return $this->success($this->lessonData($request, $topic, true), 'Lesson completed');
    }

    private function ensureLessonExists(Topic $topic): void
    {
        if (! $topic->hasLesson()) {
            throw ValidationException::withMessages([
                'lesson' => ['This topic does not have lesson content yet.'],
            ]);
        }
    }

    private function lessonData(Request $request, Topic $topic, bool $isCompleted = false): array
    {
        $isCompleted = $isCompleted || TopicLessonCompletion::where('user_id', $request->user()->id)
            ->where('topic_id', $topic->id)
            ->exists();

        return [
            'id' => $topic->id,
            'name' => $topic->name,
            'description' => $topic->description,
            'lesson' => [
                'title' => $topic->lesson_title,
                'summary' => $topic->lesson_summary,
                'sections' => $topic->lesson_content,
                'is_completed' => $isCompleted,
            ],
        ];
    }
}
