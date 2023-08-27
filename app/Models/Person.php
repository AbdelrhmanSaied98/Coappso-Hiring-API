<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Person extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'persons';
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'verification_code',
        'sending_code_time',
        'isBlocked',
        'ban_times',
        'birth_date',
        'image',
        'device_token',
        'gender',
        'country',
        'city',
        'min_salary',
        'cv',
        'carer_level_id',
        'experience_year_id',
        'job_search_status_id',
        'nationality',
        'isHideSalary',
        'military_status',
        'marital_status',
        'derive_licence',
        'refresh_token',
    ];
    protected $hidden = [
        'password',
    ];



    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function experiences()
    {
        return $this->hasMany(Experience::class,'person_id');
    }


    public function carer_level()
    {
        return $this->belongsTo(Carer_Level::class,'carer_level_id');
    }

    public function experience_year()
    {
        return $this->belongsTo(Experience_Year::class,'experience_year_id');
    }


    public function job_search_status()
    {
        return $this->belongsTo(Job_Search_Status::class,'job_search_status_id');
    }

    public function person_categories()
    {
        return $this->hasMany(Person_Category::class,'person_id');
    }

    public function person_job_descriptions()
    {
        return $this->hasMany(Person_Job_Description::class,'person_id');
    }

    public function person_job_types()
    {
        return $this->hasMany(Person_Job_Type::class,'person_id');
    }

    public function person_languages()
    {
        return $this->hasMany(Person_Language::class,'person_id');
    }

    public function person_skills()
    {
        return $this->hasMany(Person_Skill::class,'person_id');
    }

    public function person_study_fields()
    {
        return $this->hasMany(Person_Study_Field::class,'person_id');
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class,'person_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class,'person_id');
    }
    public function tournament_competitors()
    {
        return $this->hasMany(Tournament_Competitor::class,'person_id');
    }

}
