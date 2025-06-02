<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'deviceId',
        'name',
        'type',
        'auto_mode'
    ];

    public function payloads()
    {
        return $this->hasMany(Payload::class, 'deviceId', 'deviceId');
    }
}
