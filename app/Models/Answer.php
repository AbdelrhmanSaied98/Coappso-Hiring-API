<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $table = 'answers';

    protected $fillable = [
        'id',
        'job_form_id',
        'applicant_id',
        'answer',
    ];

    public function job_form()
    {
        return $this->belongsTo(Job_Form::class,'job_form_id');
    }
    public function applicant()
    {
        return $this->belongsTo(Applicant::class,'applicant_id');
    }

}
