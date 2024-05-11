<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;


class Post extends Model
{
    use HasFactory;
    use softDeletes;


    protected $fillable=['user_id','body','group_id'];

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

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'object');
    }

    public function comments(): HasMany
    {
        return $this->HasMany(PostComment::class)->latest();
    }
    public function latest5Comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }





    public static function postsForTimeline($userId): Builder
    {
        return Post::query() // SELECT * FROM posts
            ->withCount('reactions') // Counts all reactions associated with each post
            ->with([
                'comments' => function ($query) {
                    $query->withCount('reactions'); // Counts reactions for each comment
                },
                'reactions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId); // Filters reactions by user_id
                }
            ])
            ->latest(); // Orders posts by created_at in descending order
    }
}
