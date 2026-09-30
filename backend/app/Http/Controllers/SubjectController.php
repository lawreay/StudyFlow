<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends ApiController
{
    public function index()
    {
        $subjects = Subject::with(['topics' => fn ($query) => $query->withCount('questions')])->get();

        return $this->success($subjects, 'Subjects loaded');
    }

    public function show(Subject $subject)
    {
        $subject->load(['topics' => fn ($query) => $query->withCount('questions')]);

        return $this->success($subject, 'Subject loaded');
    }
}
