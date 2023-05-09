<?php

namespace App\Models\User;
use Illuminate\Database\Eloquent\Model;

class VisitorPosition extends Model
{
    protected $fillable = [
        'user_id',
        'accessibility',
        'device_id',
        'accuracy',
        'altitude',
        'heading',
        'latitude',
        'longitude',
        'speed',
        'timestamp',
        'timeout',
        'position_unavailable',
        'permission_denied',
        'message',
        'code',
        'location_status',
        'network'
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
