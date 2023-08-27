<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;
    protected $table = 'grades';

    protected $fillable = [
        'id',
        'name',
    ];

    public function person_study_fields()
    {
        return $this->hasMany(Person_Study_Field::class,'grade_id');
    }
}
