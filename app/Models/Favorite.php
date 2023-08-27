<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;
    protected $table = 'favorites';

    protected $fillable = [
        'person_id',
        'job_id'
    ];
    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }
    public function job()
    {
        return $this->belongsTo(Job::class,'job_id');
    }
}
