<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;

class AdminDashboardController extends ApiController
{
    public function __invoke()
    {
        return $this->success([
            'users_count' => User::count(),
            'subjects_count' => Subject::count(),
            'topics_count' => Topic::count(),
            'questions_count' => Question::count(),
        ], 'Administrator dashboard loaded');
    }
}