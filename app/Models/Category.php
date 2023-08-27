<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';

    protected $fillable = [
        'id',
        'name',
    ];

    public function experiences()
    {
        return $this->hasMany(Experience::class,'category_id');
    }

    public function person_categories()
    {
        return $this->hasMany(Person_Category::class,'category_id');
    }
    public function company_categories()
    {
        return $this->hasMany(Company_Category::class,'category_id');
    }

    public function job_categories()
    {
        return $this->hasMany(Job_Category::class,'category_id');
    }


}
