<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitation extends Model
{
    protected $fillable = [
    'vip_name',
    'organization',
    'vip_email',
    'vip_phone',
    'attendance_status',
    'vehicle_registration',
    'estimated_arrival',
    'estimated_departure',
    'submitted_by_name',
    'submitted_by_email',
    'submitted_at',
    'expires_at',
];

    protected $casts = [
        'submitted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(InvitationContact::class);
    }
}