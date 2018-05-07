<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
    	'title' => 'desc',
    	'desc' => 'string',
    ];

    protected $fillable = [
    	'title',
    	'desc',
    	'rating'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function destiny()
    {
    	return $this->belongsTo(Destiny::class);
    }
}
