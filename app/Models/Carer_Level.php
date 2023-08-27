<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carer_Level extends Model
{
    use HasFactory;

    protected $table = 'carer_levels';

    protected $fillable = [
        'id',
        'name',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class,'carer_level_id');
    }
    public function jobs()
    {
        return $this->hasMany(Job::class,'carer_level_id');
    }
}
