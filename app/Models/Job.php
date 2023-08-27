<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $table = 'jobs';
    protected $fillable = [
        'job_description_id',
        'job_type_id',
        'country',
        'city',
        'carer_level_id',
        'education_level_id',
        'company_id',
        'experience_min',
        'experience_max',
        'salary_min',
        'salary_max',
        'isHideSalary',
        'additionSalaryDetails',
        'number_of_vacancies',
        'job_details',
    ];



    public function job_description()
    {
        return $this->belongsTo(Job_Description::class,'job_description_id');
    }

    public function education_level()
    {
        return $this->belongsTo(Education_Level::class,'education_level_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id');
    }

    public function job_type()
    {
        return $this->belongsTo(Job_Type::class,'job_type_id');
    }

    public function carer_level()
    {
        return $this->belongsTo(Carer_Level::class,'carer_level_id');
    }

    public function job_categories()
    {
        return $this->hasMany(Job_Category::class,'job_id');
    }

    public function job_skills()
    {
        return $this->hasMany(Job_Skill::class,'job_id');
    }

    public function job_forms()
    {
        return $this->hasMany(Job_Form::class,'job_id');
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class,'job_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class,'job_id');
    }

    public function short_lists()
    {
        return $this->hasMany(Short_List::class,'job_id');
    }
}
