<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends ApiController
{
    public function index(Request $request)
    {
        $query = Question::with('options')->with('topic');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }

        return $this->success($query->get(), 'Questions loaded');
    }

    public function show(Question $question)
    {
        $question->load(['options', 'topic', 'subject']);

        return $this->success($question, 'Question loaded');
    }
}
