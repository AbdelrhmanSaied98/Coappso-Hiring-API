<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament_Competitor_Answer extends Model
{
    use HasFactory;

    protected $table = 'tournament_competitor_answers';

    protected $fillable = [
        'id',
        'tournament_competitor_id',
        'tournament_question_id',
        'answer',
    ];

    public function tournament_competitor()
    {
        return $this->belongsTo(Tournament_Competitor::class,'tournament_competitor_id');
    }

    public function tournament_question()
    {
        return $this->belongsTo(Tournament_Question::class,'tournament_question_id');
    }
}
