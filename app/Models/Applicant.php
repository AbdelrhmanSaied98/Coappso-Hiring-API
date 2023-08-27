<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $table = 'applicants';

    protected $fillable = [
        'id',
        'job_id',
        'person_id',
        'date',
        'status',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }
    public function job()
    {
        return $this->belongsTo(Job::class,'job_id');
    }
    public function answers()
    {
        return $this->hasMany(Answer::class, 'applicant_id');
    }
    public function short_lists()
    {
        return $this->hasMany(Short_List::class,'applicant_id');
    }
}
