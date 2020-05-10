<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccessToken extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_id', 'token', 'expired_at',
    ];

    public function generateToken($expire)
    {
        $this->expired_at = $expire;
        $this->token = Str::random(32);
    }
}
