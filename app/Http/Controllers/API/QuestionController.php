<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with(['answers' => function ($query) {
            $query->withCount('responses')
                ->with(['voters' => function ($query) {
                    $query->select('employees.id', 'employees.firstname', 'employees.lastname');
                }])
                ->orderBy('id');
        }])
            ->withCount('responses')
            ->latest()
            ->get();

        $questions->each(function (Question $question) {
            $question->answers->each(function ($answer) {
                // Keep responses_count for backwards compatibility and expose the
                // existing count under the API's more descriptive votes field too.
                $answer->setAttribute('votes', $answer->responses_count);
                $answer->setAttribute('voters', $answer->voters->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'firstname' => $employee->firstname,
                        'lastname' => $employee->lastname,
                    ];
                })->values());
                $answer->unsetRelation('voters');
            });
        });

        return $questions;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:255'],
            'answers' => ['required', 'array', 'min:2', 'max:10'],
            'answers.*' => ['required', 'string', 'max:255', 'distinct'],
        ]);

        $question = Question::create(['text' => $data['text']]);
        $question->answers()->createMany(
            collect($data['answers'])->map(function ($answer) {
                return ['text' => $answer];
            })->all()
        );

        return response()->json($question->load('answers'), 201);
    }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:255'],
        ]);

        $question->update($data);

        return $question->load('answers');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->noContent();
    }

    public function activate(Question $question)
    {
        $question->update(['is_active' => true]);

        return $question;
    }

    public function deactivate(Question $question)
    {
        $question->update(['is_active' => false]);

        return $question;
    }
}
