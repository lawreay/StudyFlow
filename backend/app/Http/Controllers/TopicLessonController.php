<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\TopicLessonCompletion;
use App\Models\TopicLessonPracticeCompletion;
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
        $this->ensurePracticeCompleted($request, $topic);

        TopicLessonCompletion::firstOrCreate(
            ['user_id' => $request->user()->id, 'topic_id' => $topic->id],
            ['completed_at' => now()],
        );

        return $this->success($this->lessonData($request, $topic, true), 'Lesson completed');
    }

    public function checkPractice(Request $request, Topic $topic)
    {
        $this->ensureLessonExists($topic);
        $this->ensurePracticeExists($topic);

        $validated = $request->validate([
            'selected_option_id' => ['required', 'string', 'max:100'],
        ]);
        $practice = $topic->lesson_practice;
        $selectedOption = collect($practice['options'])->firstWhere('id', $validated['selected_option_id']);

        if ($selectedOption === null) {
            throw ValidationException::withMessages([
                'selected_option_id' => ['Choose one of the available answers.'],
            ]);
        }

        $isCorrect = hash_equals((string) $practice['correct_option_id'], (string) $selectedOption['id']);

        if ($isCorrect) {
            TopicLessonPracticeCompletion::firstOrCreate(
                ['user_id' => $request->user()->id, 'topic_id' => $topic->id],
                ['completed_at' => now()],
            );
        }

        return $this->success([
            'is_correct' => $isCorrect,
            'feedback' => $isCorrect ? $practice['correct_feedback'] : $practice['incorrect_feedback'],
            'is_practice_completed' => $isCorrect || TopicLessonPracticeCompletion::where('user_id', $request->user()->id)
                ->where('topic_id', $topic->id)
                ->exists(),
        ], $isCorrect ? 'Mission check passed' : 'Try the mission check again');
    }

    private function ensureLessonExists(Topic $topic): void
    {
        if (! $topic->hasLesson()) {
            throw ValidationException::withMessages([
                'lesson' => ['This topic does not have lesson content yet.'],
            ]);
        }
    }

    private function ensurePracticeExists(Topic $topic): void
    {
        if (! $topic->hasLessonPractice()) {
            throw ValidationException::withMessages([
                'practice' => ['This topic does not have a mission check yet.'],
            ]);
        }
    }

    private function ensurePracticeCompleted(Request $request, Topic $topic): void
    {
        if ($topic->hasLessonPractice() && ! TopicLessonPracticeCompletion::where('user_id', $request->user()->id)
            ->where('topic_id', $topic->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'practice' => ['Pass the mission check before completing this lesson.'],
            ]);
        }
    }

    private function lessonData(Request $request, Topic $topic, bool $isCompleted = false): array
    {
        $isCompleted = $isCompleted || TopicLessonCompletion::where('user_id', $request->user()->id)
            ->where('topic_id', $topic->id)
            ->exists();
        $isPracticeCompleted = TopicLessonPracticeCompletion::where('user_id', $request->user()->id)
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
                'practice' => $topic->hasLessonPractice() ? [
                    'question' => $topic->lesson_practice['question'],
                    'options' => $topic->lesson_practice['options'],
                    'is_completed' => $isPracticeCompleted,
                ] : null,
            ],
        ];
    }
}
