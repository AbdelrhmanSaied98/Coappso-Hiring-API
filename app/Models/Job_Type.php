<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Type extends Model
{
    use HasFactory;
    protected $table = 'job_types';

    protected $fillable = [
        'id',
        'name',
    ];

    public function experiences()
    {
        return $this->hasMany(Experience::class,'job_type_id');
    }

    public function person_job_types()
    {
        return $this->hasMany(Person_Job_Type::class,'job_type_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class,'job_type_id');
    }

}
