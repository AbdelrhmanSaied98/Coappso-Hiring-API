<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Description extends Model
{
    use HasFactory;

    protected $table = 'job_descriptions';

    protected $fillable = [
        'id',
        'name',
    ];

    public function person_job_descriptions()
    {
        return $this->hasMany(Person_Job_Description::class,'job_description_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class,'job_description_id');
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class,'job_description_id');
    }

}
