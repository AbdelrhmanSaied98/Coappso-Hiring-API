<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $table = 'experiences';

    protected $fillable = [
        'id',
        'job_title',
        'company_name',
        'category_id',
        'job_type_id',
        'isCurrent',
        'start_from',
        'start_to',
        'person_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    public function job_type()
    {
        return $this->belongsTo(Job_Type::class,'job_type_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

}
