<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person_Job_Type extends Model
{
    use HasFactory;

    protected $table = 'person_job_types';

    protected $fillable = [
        'id',
        'person_id',
        'job_type_id',
    ];


    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function job_type()
    {
        return $this->belongsTo(Job_Type::class,'job_type_id');
    }


}
