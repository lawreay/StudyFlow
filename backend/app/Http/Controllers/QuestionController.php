<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;

class QuestionController extends ApiController
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['sometimes', 'integer', 'exists:subjects,id'],
            'topic_id' => ['sometimes', 'integer', 'exists:topics,id'],
        ]);

        $query = Question::with(['options', 'topic']);

        if (isset($validated['subject_id'])) {
            $query->where('subject_id', $validated['subject_id']);
        }

        if (isset($validated['topic_id'])) {
            $query->where('topic_id', $validated['topic_id']);
        }

        return $this->success(QuestionResource::collection($query->get())->resolve(), 'Questions loaded');
    }

    public function show(Question $question)
    {
        $question->load(['options', 'topic', 'subject']);

        return $this->success((new QuestionResource($question))->resolve(), 'Question loaded');
    }
}
