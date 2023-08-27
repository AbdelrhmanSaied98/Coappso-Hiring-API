<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person_Job_Description extends Model
{
    use HasFactory;

    protected $table = 'person_job_desciptions';

    protected $fillable = [
        'id',
        'person_id',
        'job_description_id',
    ];


    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function job_description()
    {
        return $this->belongsTo(Job_Description::class,'job_description_id');
    }



}
