<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuestionController extends Controller
{
    //    
    public function index()
    {
        return Question::with('answers')->where('is_active', true)->take(2)->get();
    }

    public function store(Request $request)
    {
        $question = Question::create($request->only('text'));
        return response()->json($question, 201);
    }

    public function show(Question $question)
    {
        return $question->load('answers');
    }

    public function update(Request $request, Question $question)
    {
        $question->update($request->only('text'));
        return response()->json($question);
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return response()->json(null, 204);
    }

    public function activate(Question $question)
    {
        $question->update(['is_active' => true]);
        return response()->json($question);
    }

    public function deactivate(Question $question)
    {
        $question->update(['is_active' => false]);
        return response()->json($question);
    }
}
