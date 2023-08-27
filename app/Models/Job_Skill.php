<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Skill extends Model
{
    use HasFactory;

    protected $table = 'job_skills';

    protected $fillable = [
        'id',
        'job_id',
        'skill_id',
    ];


    public function job()
    {
        return $this->belongsTo(Job::class,'job_id');
    }

    public function skill()
    {
        return $this->belongsTo(Skills::class,'skill_id');
    }

}
