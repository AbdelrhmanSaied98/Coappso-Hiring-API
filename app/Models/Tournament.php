<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;
    protected $table = 'tournaments';

    protected $fillable = [
        'id',
        'job_description_id',
        'country',
        'city',
        'experience_min',
        'experience_max',
    ];
    public function job_description()
    {
        return $this->belongsTo(Job_Description::class,'job_description_id');
    }

    public function tournament_competitors()
    {
        return $this->hasMany(Tournament_Competitor::class,'tournament_id');
    }

    public function tournament_questions()
    {
        return $this->hasMany(Tournament_Question::class,'tournament_id');
    }
}
