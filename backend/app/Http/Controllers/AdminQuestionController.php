<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminQuestionResource;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminQuestionController extends ApiController
{
    private const DIFFICULTIES = ['Easy', 'Medium', 'Hard'];

    private const CSV_HEADER = [
        'question_text', 'difficulty', 'points', 'option_a', 'option_b',
        'option_c', 'option_d', 'correct_option',
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => ['sometimes', 'integer', 'exists:topics,id'],
            'difficulty' => ['sometimes', Rule::in(self::DIFFICULTIES)],
            'search' => ['sometimes', 'string', 'max:200'],
        ]);

        $questions = Question::with(['topic', 'options'])
            ->when(isset($validated['topic_id']), fn ($query) => $query->where('topic_id', $validated['topic_id']))
            ->when(isset($validated['difficulty']), fn ($query) => $query->where('difficulty', $validated['difficulty']))
            ->when(isset($validated['search']), fn ($query) => $query->where('question_text', 'like', '%'.$validated['search'].'%'))
            ->orderBy('id')
            ->get();

        return $this->success(AdminQuestionResource::collection($questions)->resolve(), 'Questions loaded');
    }

    public function show(Question $question)
    {
        $question->load(['topic', 'options']);

        return $this->success((new AdminQuestionResource($question))->resolve(), 'Question loaded');
    }

    public function store(Request $request)
    {
        $validated = $this->validateQuestion($request);
        $topic = Topic::findOrFail($validated['topic_id']);

        $question = DB::transaction(fn () => $this->persistQuestion(new Question(), $topic, $validated));
        $question->load(['topic', 'options']);

        return $this->success((new AdminQuestionResource($question))->resolve(), 'Question created', 201);
    }

    public function update(Request $request, Question $question)
    {
        $validated = $this->validateQuestion($request);
        $topic = Topic::findOrFail($validated['topic_id']);

        DB::transaction(function () use ($question, $topic, $validated): void {
            $this->persistQuestion($question, $topic, $validated);
        });

        $question->load(['topic', 'options']);

        return $this->success((new AdminQuestionResource($question))->resolve(), 'Question updated');
    }

    public function destroy(Question $question)
    {
        if (DB::table('attempt_questions')->where('question_id', $question->id)->exists()) {
            throw ValidationException::withMessages([
                'question' => ['Questions included in quiz attempts cannot be deleted.'],
            ]);
        }

        $question->delete();

        return $this->success([], 'Question deleted');
    }

    public function import(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = $this->readAndValidateCsv($validated['file']);

        $questions = DB::transaction(function () use ($rows, $topic) {
            return collect($rows)->map(function (array $row) use ($topic): Question {
                $question = Question::create([
                    'subject_id' => $topic->subject_id,
                    'topic_id' => $topic->id,
                    'question_text' => $row['question_text'],
                    'difficulty' => $row['difficulty'],
                    'points' => (int) $row['points'],
                ]);

                foreach (['a', 'b', 'c', 'd'] as $letter) {
                    $question->options()->create([
                        'option_text' => $row['option_'.$letter],
                        'is_correct' => strtolower($row['correct_option']) === $letter,
                    ]);
                }

                return $question;
            });
        });

        return $this->success(['imported_count' => $questions->count()], 'Questions imported', 201);
    }

    private function validateQuestion(Request $request): array
    {
        $validated = $request->validate([
            'topic_id' => ['required', 'integer', 'exists:topics,id'],
            'question_text' => ['required', 'string', 'max:2000'],
            'difficulty' => ['required', Rule::in(self::DIFFICULTIES)],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'explanation' => ['nullable', 'string', 'max:5000'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*.option_text' => ['required', 'string', 'max:255'],
            'options.*.is_correct' => ['required', 'boolean'],
        ]);

        if (collect($validated['options'])->where('is_correct', true)->count() !== 1) {
            throw ValidationException::withMessages([
                'options' => ['Exactly one option must be marked correct.'],
            ]);
        }

        return $validated;
    }

    private function persistQuestion(Question $question, Topic $topic, array $validated): Question
    {
        $question->fill([
            'subject_id' => $topic->subject_id,
            'topic_id' => $topic->id,
            'question_text' => $validated['question_text'],
            'difficulty' => $validated['difficulty'],
            'points' => $validated['points'],
            'explanation' => $validated['explanation'] ?? null,
        ])->save();

        $question->options()->delete();
        $question->options()->createMany($validated['options']);

        return $question;
    }

    /** @return array<int, array<string, string>> */
    private function readAndValidateCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages(['file' => ['The CSV file could not be read.']]);
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw ValidationException::withMessages(['file' => ['The CSV file is empty.']]);
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);

            if (array_map('trim', $header) !== self::CSV_HEADER) {
                throw ValidationException::withMessages([
                    'file' => ['CSV header must be: '.implode(',', self::CSV_HEADER)],
                ]);
            }

            $rows = [];
            $errors = [];
            $line = 1;

            while (($values = fgetcsv($handle)) !== false) {
                $line++;

                if ($values === [null] || count(array_filter($values, fn ($value) => $value !== null && trim($value) !== '')) === 0) {
                    continue;
                }

                if (count($values) !== count(self::CSV_HEADER)) {
                    $errors["row_{$line}"] = ['Expected exactly eight CSV columns.'];
                    continue;
                }

                $row = array_combine(self::CSV_HEADER, array_map('trim', $values));
                $row['difficulty'] = ucfirst(strtolower($row['difficulty']));
                $row['correct_option'] = strtolower($row['correct_option']);
                $validator = Validator::make($row, [
                    'question_text' => ['required', 'string', 'max:2000'],
                    'difficulty' => ['required', Rule::in(self::DIFFICULTIES)],
                    'points' => ['required', 'integer', 'min:1', 'max:100'],
                    'option_a' => ['required', 'string', 'max:255'],
                    'option_b' => ['required', 'string', 'max:255'],
                    'option_c' => ['required', 'string', 'max:255'],
                    'option_d' => ['required', 'string', 'max:255'],
                    'correct_option' => ['required', Rule::in(['a', 'b', 'c', 'd'])],
                ]);

                if ($validator->fails()) {
                    $errors["row_{$line}"] = $validator->errors()->all();
                    continue;
                }

                $rows[] = $row;

                if (count($rows) + count($errors) > 500) {
                    $errors['file'] = ['A CSV import can contain at most 500 data rows.'];
                    break;
                }
            }
        } finally {
            fclose($handle);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => ['The CSV file contains no question rows.']]);
        }

        return $rows;
    }
}