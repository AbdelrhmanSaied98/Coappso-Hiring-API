<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament_Competitor extends Model
{
    use HasFactory;
    protected $table = 'tournament_competitors';

    protected $fillable = [
        'id',
        'tournament_id',
        'person_id',
        'rank',
        'points',
        'timer',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class,'tournament_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function tournament_competitor_answers()
    {
        return $this->hasMany(Tournament_Competitor_Answer::class,'tournament_competitor_id');
    }
}
