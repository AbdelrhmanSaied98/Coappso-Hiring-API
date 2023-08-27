<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience_Year extends Model
{
    use HasFactory;

    protected $table = 'experience_years';

    protected $fillable = [
        'id',
        'name',
        'experience_year',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class,'experience_year_id');
    }
}
