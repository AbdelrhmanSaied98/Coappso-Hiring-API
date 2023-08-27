<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Search_Status extends Model
{
    use HasFactory;

    protected $table = 'job_search_status';

    protected $fillable = [
        'id',
        'name',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class,'job_search_status_id');
    }

}
