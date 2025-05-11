<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payload extends Model
{
    protected $fillable = [
        'deviceId',
        'data'
    ];



    public function device()
    {
        return $this->belongsTo(Device::class, 'deviceId', 'deviceId');
    }
}