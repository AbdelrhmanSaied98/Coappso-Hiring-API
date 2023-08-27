<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Company extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $table = 'companies';
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'verification_code',
        'sending_code_time',
        'isBlocked',
        'ban_times',
        'image',
        'device_token',
        'company_name',
        'company_size',
        'title',
        'country',
        'city',
        'description',
        'website',
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

    public function jobs()
    {
        return $this->hasMany(Job::class,'company_id');
    }

    public function company_categories()
    {
        return $this->hasMany(Company_Category::class,'company_id');
    }



}
