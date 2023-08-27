<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person_Category extends Model
{
    use HasFactory;

    protected $table = 'person_categories';

    protected $fillable = [
        'id',
        'person_id',
        'category_id',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
