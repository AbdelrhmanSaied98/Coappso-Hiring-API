<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person_Study_Field extends Model
{
    use HasFactory;

    protected $table = 'person_study_fields';

    protected $fillable = [
        'id',
        'person_id',
        'job_study_field_id',
        'institution_name',
        'certification_name',
        'language_of_study',
        'graduation_year',
        'grade_id',
        'education_level_id'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function job_study_field()
    {
        return $this->belongsTo(Job_Study_Field::class,'job_study_field_id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class,'grade_id');
    }

    public function education_level()
    {
        return $this->belongsTo(Education_Level::class,'education_level_id');
    }


}
