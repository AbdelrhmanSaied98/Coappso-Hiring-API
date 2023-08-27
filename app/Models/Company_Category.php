<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_Category extends Model
{
    use HasFactory;


    protected $table = 'company_categories';

    protected $fillable = [
        'id',
        'company_id',
        'category_id',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

}
