<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'deviceId',
        'name',
        'type'
    ];

    public function payloads()
    {
        return $this->hasMany(Payload::class, 'deviceId', 'deviceId');
    }
}
