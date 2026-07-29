<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(Request $request, Question $question)
    {
        $data = $request->validate(['text' => ['required', 'string', 'max:255']]);

        return response()->json($question->answers()->create($data), 201);
    }

    public function update(Request $request, Answer $answer)
    {
        $data = $request->validate(['text' => ['required', 'string', 'max:255']]);
        $answer->update($data);

        return $answer;
    }

    public function destroy(Answer $answer)
    {
        abort_if($answer->responses()->exists(), 422, 'Bereits verwendete Antwortoptionen können nicht gelöscht werden.');
        abort_if($answer->question->answers()->count() <= 2, 422, 'Eine Umfrage benötigt mindestens zwei Antwortoptionen.');

        $answer->delete();

        return response()->noContent();
    }
}
