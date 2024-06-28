<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuestionResponseController extends Controller
{
    public function store(Request $request, Question $question, Answer $answer)
    {
        // Ensure the question is active
        if (!$question->is_active) {
            return response()->json(['message' => 'This question is currently inactive.'], 403);
        }

        // Check if the employee has already answered
        $employeeId = Auth::id(); // Assumes employee is authenticated and uses Laravel's Auth
        $existingResponse = QuestionResponse::where('question_id', $question->id)
                                            ->where('employee_id', $employeeId)
                                            ->first();

        if ($existingResponse) {
            return response()->json(['message' => 'You have already responded to this question.'], 403);
        }

        // Record the new response
        $response = new QuestionResponse([
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'employee_id' => $employeeId,
        ]);
        $response->save();

        return response()->json($response, 201);
    }
}