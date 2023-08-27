<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament_Question extends Model
{
    use HasFactory;

    protected $table = 'tournament_questions';

    protected $fillable = [
        'id',
        'tournament_id',
        'title',
        'right_answer',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class,'tournament_id');
    }

    public function tournament_competitor_answers()
    {
        return $this->hasMany(Tournament_Competitor_Answer::class,'tournament_question_id');
    }

    public function tournament_question_chooses()
    {
        return $this->hasMany(Tournament_Question_Choose::class,'tournament_question_id');
    }
}
