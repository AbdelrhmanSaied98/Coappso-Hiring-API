<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Study_Field extends Model
{
    use HasFactory;

    protected $table = 'job_study_fields';

    protected $fillable = [
        'id',
        'name',
    ];

    public function person_study_fields()
    {
        return $this->hasMany(Person_Study_Field::class,'job_study_field_id');
    }
}
