<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person_Language extends Model
{
    use HasFactory;
    protected $table = 'person_languages';

    protected $fillable = [
        'id',
        'person_id',
        'language_id',
        'proficiency'
    ];


    public function person()
    {
        return $this->belongsTo(Person::class,'person_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class,'language_id');
    }

}
