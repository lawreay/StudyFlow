<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttemptResource;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\PlayerProgress;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttemptController extends ApiController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
        ]);

        $activeAttempt = Attempt::where('user_id', $request->user()->id)
            ->where('topic_id', $validated['topic_id'])
            ->whereNull('completed_at')
            ->latest('id')
            ->first();

        if ($activeAttempt) {
            $activeAttempt->load(['questions.options', 'questions.topic', 'answers.question', 'answers.selectedOption']);

            return $this->success((new AttemptResource($activeAttempt))->resolve(), 'Attempt resumed');
        }

        $topic = Topic::with('questions.options')->findOrFail($validated['topic_id']);

        if ($topic->questions->isEmpty()) {
            throw ValidationException::withMessages([
                'topic_id' => ['This topic does not have any questions yet.'],
            ]);
        }

        $attempt = DB::transaction(function () use ($request, $topic): Attempt {
            $attempt = Attempt::create([
                'user_id' => $request->user()->id,
                'subject_id' => $topic->subject_id,
                'topic_id' => $topic->id,
                'total_questions' => $topic->questions->count(),
            ]);

            $attempt->started_at = now();
            $attempt->save();
            $attempt->questions()->attach($topic->questions->mapWithKeys(
                fn (Question $question, int $position) => [$question->id => [
                    'points' => $question->points,
                    'position' => $position,
                ]],
            )->all());

            return $attempt;
        });

        $attempt->refresh()->load(['questions.options', 'questions.topic', 'answers.question', 'answers.selectedOption']);

        return $this->success((new AttemptResource($attempt))->resolve(), 'Attempt started', 201);
    }

    public function submitAnswer(Request $request, int $attempt)
    {
        $validated = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'selected_option_id' => ['required', 'integer', 'exists:question_options,id'],
        ]);

        $answerData = DB::transaction(function () use ($request, $attempt, $validated): array {
            $attemptRecord = Attempt::where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->findOrFail($attempt);

            if ($attemptRecord->completed_at !== null) {
                throw ValidationException::withMessages([
                    'attempt' => ['This attempt has already been completed.'],
                ]);
            }

            $question = Question::findOrFail($validated['question_id']);
            $pivot = $attemptRecord->questions()
                ->where('questions.id', $question->id)
                ->first();

            if (! $pivot) {
                throw ValidationException::withMessages([
                    'question_id' => ['This question is not part of the attempt.'],
                ]);
            }

            $selectedOption = QuestionOption::where('question_id', $question->id)
                ->find($validated['selected_option_id']);

            if (! $selectedOption) {
                throw ValidationException::withMessages([
                    'selected_option_id' => ['The selected option does not belong to this question.'],
                ]);
            }

            if ($attemptRecord->answers()->where('question_id', $question->id)->exists()) {
                throw ValidationException::withMessages([
                    'question_id' => ['An answer has already been submitted for this question.'],
                ]);
            }

            $correct = (bool) $selectedOption->is_correct;
            $answer = AttemptAnswer::create([
                'attempt_id' => $attemptRecord->id,
                'question_id' => $question->id,
                'selected_option_id' => $selectedOption->id,
                'is_correct' => $correct,
                'marks_earned' => $correct ? (int) $pivot->pivot->points : 0,
            ]);

            $answers = $attemptRecord->answers()->get();
            $completed = $answers->count() === $attemptRecord->total_questions;
            $attemptRecord->update([
                'score' => $answers->sum('marks_earned'),
                'correct_answers' => $answers->where('is_correct', true)->count(),
                'duration_seconds' => max(0, (int) $attemptRecord->started_at?->diffInSeconds(now())),
                'completed_at' => $completed ? now() : null,
            ]);

            $progress = PlayerProgress::firstOrNew(['user_id' => $request->user()->id]);
            $progress->subject_id = $attemptRecord->subject_id;
            $progress->topic_id = $attemptRecord->topic_id;
            $progress->completed_questions = (int) $progress->completed_questions + 1;
            $progress->xp = (int) $progress->xp + ($correct ? 10 : 0);
            $progress->level = match (true) {
                $progress->xp >= 500 => 4,
                $progress->xp >= 250 => 3,
                $progress->xp >= 100 => 2,
                default => 1,
            };
            $totalAnswered = AttemptAnswer::whereHas('attempt', fn ($query) => $query->where('user_id', $request->user()->id))->count();
            $totalCorrect = AttemptAnswer::whereHas('attempt', fn ($query) => $query->where('user_id', $request->user()->id))
                ->where('is_correct', true)
                ->count();
            $progress->accuracy = round(($totalCorrect / $totalAnswered) * 100, 2);
            $progress->score = (int) $progress->score + (int) $answer->marks_earned;
            $progress->save();

            $attemptRecord->load(['questions.options', 'questions.topic', 'answers.question', 'answers.selectedOption']);

            return [$answer, $question, $attemptRecord, $progress];
        });

        [$answer, $question, $attemptRecord, $progress] = $answerData;

        return $this->success([
            'answer' => [
                'question_id' => $answer->question_id,
                'selected_option_id' => $answer->selected_option_id,
                'is_correct' => $answer->is_correct,
                'marks_earned' => $answer->marks_earned,
                'explanation' => $question->explanation,
            ],
            'attempt' => (new AttemptResource($attemptRecord))->resolve(),
            'progress' => [
                'xp' => $progress->xp,
                'level' => $progress->level,
                'completed_questions' => $progress->completed_questions,
                'accuracy' => (float) $progress->accuracy,
                'score' => $progress->score,
            ],
        ], 'Answer saved');
    }

    public function show(Request $request, int $attempt)
    {
        $attemptRecord = Attempt::where('user_id', $request->user()->id)
            ->with(['questions.options', 'questions.topic', 'answers.question', 'answers.selectedOption'])
            ->findOrFail($attempt);

        return $this->success((new AttemptResource($attemptRecord))->resolve(), 'Attempt loaded');
    }

    public function updatePosition(Request $request, int $attempt)
    {
        $validated = $request->validate([
            'current_question_index' => ['required', 'integer', 'min:0'],
        ]);

        $attemptRecord = Attempt::where('user_id', $request->user()->id)->findOrFail($attempt);

        if ($attemptRecord->completed_at !== null) {
            throw ValidationException::withMessages([
                'attempt' => ['A completed attempt cannot be resumed.'],
            ]);
        }

        if ($validated['current_question_index'] >= $attemptRecord->total_questions) {
            throw ValidationException::withMessages([
                'current_question_index' => ['The question position is outside this attempt.'],
            ]);
        }

        $attemptRecord->update(['current_question_index' => $validated['current_question_index']]);
        $attemptRecord->load(['questions.options', 'questions.topic', 'answers.question', 'answers.selectedOption']);

        return $this->success((new AttemptResource($attemptRecord))->resolve(), 'Question position saved');
    }
}