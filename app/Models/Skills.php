<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skills extends Model
{
    use HasFactory;

    protected $table = 'skills';

    protected $fillable = [
        'id',
        'name',
    ];

    public function person_skills()
    {
        return $this->hasMany(Person_Skill::class,'skill_id');
    }

    public function job_skills()
    {
        return $this->hasMany(Job_Skill::class,'skill_id');
    }
}
