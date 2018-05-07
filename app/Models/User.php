<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends BaseModel implements AuthenticatableContract, JWTSubject
{
    // Soft delete and user authentication
    use SoftDeletes, Authenticatable;

    // When querying the user, do not expose the password
    protected $hidden = ['password', 'deleted_at', 'id'];

    public function events()
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    // jwt need to implement the method
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // jwt need to implement the method
    public function getJWTCustomClaims()
    {
        return [];
    }
}
