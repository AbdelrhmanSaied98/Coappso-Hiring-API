<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job_Category extends Model
{
    use HasFactory;

    protected $table = 'job_categories';

    protected $fillable = [
        'id',
        'job_id',
        'category_id',
    ];


    public function job()
    {
        return $this->belongsTo(Job::class,'job_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

}
