<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Destiny extends BaseModel
{
    use SoftDeletes;

    protected $table = 'destiny';
    
    protected $casts = [
    	'name' 		=> 'string',
    	'country' 	=> 'string',
    	'lat' 		=> 'string',
    	'long' 		=> 'string'
    ];

    protected $fillable = [
    	'name',
    	'country',
    	'lat',
    	'long',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
    	return $this->hasMany('App\Models\Event', 'destiny_id');
    }
}
