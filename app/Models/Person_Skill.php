<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person_Skill extends Model
{
    use HasFactory;
    protected $table = 'person_skills';

    protected $fillable = [
        'id',
        'person_id',
        'skill_id',
    ];


    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skills::class,'skill_id');
    }

}
