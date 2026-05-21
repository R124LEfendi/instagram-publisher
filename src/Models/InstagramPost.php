<?php

namespace R124LEfendi\InstagramPublisher\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstagramPost extends Model
{
    protected $fillable = [
        'user_id',
        'instagram_profile_id',
        'caption',
        'image_url',
        'ig_post_id',
        'status',
        'error_message',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    /**
     * Get the user who created this post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the profile this post was sent to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(InstagramProfile::class, 'instagram_profile_id');
    }
}
