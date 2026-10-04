<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class AdminTopicController extends ApiController
{
    public function index()
    {
        $topics = Topic::with('subject')
            ->withCount('questions')
            ->orderBy('subject_id')
            ->orderBy('name')
            ->get();

        return $this->success($topics->map(
            fn (Topic $topic): array => $this->topicData($topic),
        )->all(), 'Topics loaded');
    }

    public function show(Topic $topic)
    {
        $topic->load('subject')->loadCount('questions');

        return $this->success($this->topicData($topic), 'Topic loaded');
    }

    public function update(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lesson_title' => ['nullable', 'string', 'max:255'],
            'lesson_summary' => ['nullable', 'string', 'max:2000'],
            'lesson_content' => ['nullable', 'array', 'min:1', 'max:20'],
            'lesson_content.*.heading' => ['required_with:lesson_content', 'string', 'max:255'],
            'lesson_content.*.body' => ['required_with:lesson_content', 'string', 'max:5000'],
        ]);

        $topic->update($validated);
        $topic->load('subject')->loadCount('questions');

        return $this->success($this->topicData($topic), 'Topic updated');
    }

    private function topicData(Topic $topic): array
    {
        return [
            'id' => $topic->id,
            'subject_id' => $topic->subject_id,
            'subject_name' => $topic->subject->name,
            'name' => $topic->name,
            'description' => $topic->description,
            'lesson_title' => $topic->lesson_title,
            'lesson_summary' => $topic->lesson_summary,
            'lesson_content' => $topic->lesson_content,
            'questions_count' => $topic->questions_count,
        ];
    }
}
