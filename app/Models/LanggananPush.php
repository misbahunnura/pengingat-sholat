<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanggananPush extends Model
{
    protected $table = 'langganan_push';

    protected $fillable = [
        'user_id', 'endpoint_hash', 'endpoint', 'p256dh', 'auth', 'lat', 'lon', 'tz', 'nama',
    ];
}
