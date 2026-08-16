<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationContact extends Model
{
    protected $fillable = [
        'invitation_id',
        'name',
        'email',
        'token',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];



    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}