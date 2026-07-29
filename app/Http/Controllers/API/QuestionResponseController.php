<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionResponse;
use Illuminate\Http\Request;

class QuestionResponseController extends Controller
{
    public function active(Request $request)
    {
        $employeeId = $request->user()->id;

        return Question::where('is_active', true)
            ->with(['answers' => function ($query) {
                $query->withCount('responses')->orderBy('id');
            }])
            ->withCount('responses')
            ->get()
            ->map(function (Question $question) use ($employeeId) {
                $response = $question->responses()
                    ->where('employee_id', $employeeId)
                    ->first();

                return $this->formatQuestion($question, $response ? $response->answer_id : null);
            });
    }

    public function store(Request $request, Question $question)
    {
        abort_unless($question->is_active, 404);

        $data = $request->validate([
            'answer_id' => ['required', 'integer'],
        ]);

        abort_unless(
            $question->answers()->whereKey($data['answer_id'])->exists(),
            422,
            'Die Antwort gehört nicht zu dieser Umfrage.'
        );

        $response = QuestionResponse::updateOrCreate(
            [
                'question_id' => $question->id,
                'employee_id' => $request->user()->id,
            ],
            ['answer_id' => $data['answer_id']]
        );

        $question->load(['answers' => function ($query) {
            $query->withCount('responses')->orderBy('id');
        }])->loadCount('responses');

        return $this->formatQuestion($question, $response->answer_id);
    }

    private function formatQuestion(Question $question, $selectedAnswerId)
    {
        return [
            'id' => $question->id,
            'text' => $question->text,
            'has_responded' => $selectedAnswerId !== null,
            'selected_answer_id' => $selectedAnswerId,
            'total_responses' => $selectedAnswerId !== null ? $question->responses_count : null,
            'answers' => $question->answers->map(function ($answer) use ($question, $selectedAnswerId) {
                return [
                    'id' => $answer->id,
                    'text' => $answer->text,
                    // Results are only visible after the employee has voted.
                    'responses' => $selectedAnswerId !== null ? $answer->responses_count : null,
                    'percentage' => $selectedAnswerId !== null && $question->responses_count > 0
                        ? round(($answer->responses_count / $question->responses_count) * 100, 1)
                        : null,
                ];
            })->values(),
        ];
    }
}
