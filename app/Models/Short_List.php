<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Short_List extends Model
{
    use HasFactory;
    protected $table = 'short_lists';

    protected $fillable = [
        'job_id',
        'applicant_id',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class,'job_id');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class,'applicant_id');
    }

}
