<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;
    use softDeletes;


    protected $fillable=['user_id','body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PostAttachment::class)->latest();
    }

    public function reactions(): HasMany
    {
        return $this->HasMany(PostReaction::class);
    }

    public function comments(): HasMany
    {
        return $this->HasMany(PostComment::class)->latest();
    }
    public function latest5Comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

}
