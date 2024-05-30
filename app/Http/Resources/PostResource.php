<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Reaction;
use App\Models\Post;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $comments = $this->comments;
        return [
            'id' => $this->id,
            'body' => $this->body,
            'preview' => $this->preview,
            'preview_url' => $this->preview_url,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->user),
            'group' => new GroupResource($this->group),
            'attachments' => PostAttachmentResource::collection($this->attachments),
            'num_of_reactions' => Reaction::where('object_id', $this->id)->where('object_type', Post::class)->count(),
            'num_of_comments' => count($comments),
            'current_user_has_reaction' => $this->reactions->count() > 0,
            'comments' => self::convertCommentsIntoTree($comments)
        ];
    }

    /**
     * Convert comments into a tree structure.
     *
     * @param \App\Models\PostComment[] $comments
     * @param int|null $parentId
     * @return array
     */
    private static function convertCommentsIntoTree($comments, $parentId = null): array
    {
        $commentTree = [];

        foreach ($comments as $comment) {
            if ($comment->parent_id === $parentId) {
                // Find all comments which have parentId as $comment->id
                $children = self::convertCommentsIntoTree($comments, $comment->id);
                $comment->childComments = $children;
                $comment->numOfComments = collect($children)->sum('numOfComments') + count($children);

                $commentTree[] = new CommentResource($comment);
            }
        }

        return $commentTree;
    }
}
