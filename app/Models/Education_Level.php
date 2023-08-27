<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education_Level extends Model
{
    use HasFactory;

    protected $table = 'education_levels';

    protected $fillable = [
        'id',
        'name',
    ];

    public function person_study_fields()
    {
        return $this->hasMany(Person_Study_Field::class,'education_level_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class,'education_level_id');
    }

}
