<?php

namespace R124LEfendi\InstagramPublisher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstagramAccount extends Model
{
    protected $fillable = [
        'user_id',
        'fb_user_id',
        'name',
        'email',
        'access_token',
        'avatar',
    ];

    /**
     * Get the user that owns the Instagram account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the profiles associated with this Meta account.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(InstagramProfile::class);
    }
}
