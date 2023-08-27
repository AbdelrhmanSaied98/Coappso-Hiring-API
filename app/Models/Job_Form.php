<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Form extends Model
{
    use HasFactory;

    protected $table = 'job_forms';

    protected $fillable = [
        'id',
        'job_id',
        'question_title',
        'type',
        'chooses',
    ];


    public function job()
    {
        return $this->belongsTo(Job::class,'job_id');
    }
    public function answers()
    {
        return $this->hasMany(Answer::class, 'job_form_id');
    }

}
