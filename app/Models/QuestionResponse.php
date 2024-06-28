<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionResponse extends Model
{
    use HasFactory;
    protected $fillable = ['question_id', 'answer_id', 'employee_id'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function employee() // Assuming you have an Employee model
    {
        return $this->belongsTo(Employee::class);
    }
}
