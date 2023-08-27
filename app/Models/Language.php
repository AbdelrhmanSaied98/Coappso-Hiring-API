<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;
    protected $table = 'languages';

    protected $fillable = [
        'id',
        'name',
    ];

    public function person_languages()
    {
        return $this->hasMany(Person_Language::class,'language_id');
    }
}
