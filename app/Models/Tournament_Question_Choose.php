<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament_Question_Choose extends Model
{
    use HasFactory;
    protected $table = 'tournament_question_chooses';

    protected $fillable = [
        'id',
        'tournament_question_id',
        'choose',
    ];
    public function tournament_question()
    {
        return $this->belongsTo(Tournament_Question::class,'tournament_question_id');
    }
}
