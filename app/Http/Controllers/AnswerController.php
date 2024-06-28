<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function index(Question $question)
    {
        return $question->answers;
    }

    public function store(Request $request, Question $question)
    {
        $answer = $question->answers()->create($request->only('text'));
        return response()->json($answer, 201);
    }

    public function show(Answer $answer)
    {
        return $answer;
    }

    public function update(Request $request, Answer $answer)
    {
        $answer->update($request->only('text'));
        return response()->json($answer);
    }

    public function destroy(Answer $answer)
    {
        $answer->delete();
        return response()->json(null, 204);
    }
}
