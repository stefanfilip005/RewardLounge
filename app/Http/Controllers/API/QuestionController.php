<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return Question::with(['answers' => function ($query) {
            $query->withCount('responses')->orderBy('id');
        }])
            ->withCount('responses')
            ->latest()
            ->get();
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
