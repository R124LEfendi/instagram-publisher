<?php

namespace R124LEfendi\InstagramPublisher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstagramProfile extends Model
{
    protected $fillable = [
        'instagram_account_id',
        'instagram_profile_id',
        'instagram_username',
        'instagram_name',
        'fb_page_id',
        'fb_page_access_token',
        'avatar',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the Meta account that owns this Instagram profile.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class, 'instagram_account_id');
    }

    /**
     * Get the posts sent to this profile.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(InstagramPost::class);
    }
}
