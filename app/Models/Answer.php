<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    protected $fillable = ['question_id', 'text'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function responses()
    {
        return $this->hasMany(QuestionResponse::class);
    }

    public function getPercentageAttribute()
    {
        $totalResponses = $this->responses()->count();
        $totalQuestionResponses = $this->question->responses()->count();
        return $totalQuestionResponses > 0 ? round(($totalResponses / $totalQuestionResponses) * 100, 2) : 0;
    }
}
