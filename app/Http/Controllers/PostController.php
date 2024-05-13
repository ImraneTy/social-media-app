<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\PostAttachment;
use App\Http\Enums\ReactionEnum;
use Illuminate\Validation\Rule;
use App\Models\Reaction;
use App\Models\PostComment;
use App\Http\Resources\CommentResource;
use App\Http\Requests\UpdateCommentRequest;
use App\Notifications\CommentDeleted;
use App\Notifications\PostDeleted;
use App\Notifications\PostCreated;
use Illuminate\Support\Facades\Notification;
use App\Http\Resources\PostResource;
use App\Notifications\CommentCreated;
use App\Notifications\ReactionAddedOnPost;
use App\Models\User;
use App\Notifications\ReactionAddedOnComment;
use OpenAI\Laravel\Facades\OpenAI;



class PostController extends Controller
{

    public function view(Request $request, Post $post)
    {
        if ($post->group_id && !$post->group->hasApprovedUser(Auth::id())) {
            return inertia('Error', [
                'title' => 'Permission Denied',
                'body' => "You don't have permission to view that post"
            ])->toResponse($request)->setStatusCode(403);
        }
        $post->loadCount('reactions');
        $post->load([
            'comments' => function ($query) {
                $query->withCount('reactions'); // SELECT * FROM comments WHERE post_id IN (1, 2, 3...)
                // SELECT COUNT(*) from reactions
            },
        ]);

        return inertia('Post/View', [
            'post' => new PostResource($post)
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        DB::beginTransaction();
        $allFilePaths = [];
        try {
            $post = Post::create($data);

            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id
                ]);
            }

            DB::commit();

            $group = $post->group;

            if ($group) {
                $users = $group->approvedUsers()->where('users.id', '!=', $user->id)->get();
                Notification::send($users, new PostCreated($post, $user, $group));
            }

            $followers = $user->followers;
            Notification::send($followers, new PostCreated($post, $user, null));

        } catch (\Exception $e) {
            foreach ($allFilePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            DB::rollBack();
            throw $e;
        }

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {



        $user = $request->user();
             DB::beginTransaction();
        $allFilePaths = [];
        try {
            $data=$request->validated();
            $post->update($data);

            $deleted_ids=$data['deleted_file_ids']??[];


            $attachments = PostAttachment::query()
                ->where('post_id', $post->id)
                ->whereIn('id', $deleted_ids)
                ->get();

                foreach ($attachments as $attachment) {
                    $attachment->delete();
                }
            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id
                ]);
            }

            DB::commit();
    } catch (\Exception $e) {
        foreach ($allFilePaths as $path) {
            Storage::disk('public')->delete($path);
        }
        DB::rollBack();
        throw $e;}


        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $id = Auth::id();

        if ($post->isOwner($id) || $post->group && $post->group->isAdmin($id)) {
            $post->delete();

            if (!$post->isOwner($id)) {
                $post->user->notify(new PostDeleted($post->group));
            }

            return back();
        }

        return response("You don't have permission to delete this post", 403);
    }
    public function downloadAttachment(PostAttachment $attachment)
    {

        return response()->download(Storage::disk('public')->path($attachment->path), $attachment->name);
    }

    public function postReaction(Request $request, Post $post){
        $data = $request->validate([
            'reaction' => [Rule::enum(ReactionEnum::class)]
        ]);
        $userId=Auth::id();
        $reaction = Reaction::where('user_id', $userId)->where('object_id', $post->id)->where('object_type', Post::class)->first();

        if($reaction){
            $hasReaction=false;
            $reaction->delete();
        }else{
            $hasReaction=true;
            Reaction::create([
            'object_id'=>$post->id,
            'object_type'=>Post::class,
            'user_id'=>$userId,
            'type'=>$data['reaction'],
        ]);  


        if (!$post->isOwner($userId)) {
            $user = User::where('id', $userId)->first();
            $post->user->notify(new ReactionAddedOnPost($post, $user));
        }
    }

        $reactions = Reaction::where('object_id', $post->id)->where('object_type', post::class)->count();
        return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction
        ]);
    }
    public function createComment(Request $request, Post $post)
    {
        $data = $request->validate([
            'comment' => ['required'],
            'parent_id'=>['nullable','exists:post_comments,id']
        ]);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id(),
            'parent_id'=>$data['parent_id']?: null
        ]);

        $post = $comment->post;
        $post->user->notify(new CommentCreated($comment, $post));

        return response(new CommentResource($comment), 201);

    }

    public function deleteComment(PostComment $comment){
        $post = $comment->post;
        $id = Auth::id();
        if ( $post->isOwner($id)||$post->group && $post->group->isAdmin($id)){
            $comment->delete();

            if (!$comment->isOwner($id)) {
                $comment->user->notify(new CommentDeleted($comment, $post));
            }

            return response('',204);

        }
            
            return response("you don't have permision to delete this comment.",403);

       
    }


    public function updateComment(UpdateCommentRequest $request, PostComment $comment)
    {
        $data = $request->validated();

        $comment->update([
            'comment' => nl2br($data['comment'])
        ]);

        return new CommentResource($comment);
    }



    public function commentReaction(Request $request, PostComment $comment){
    $data = $request->validate([
        'reaction' => [Rule::enum(ReactionEnum::class)]
    ]);

    $userId = Auth::id();
    $reaction = Reaction::where('user_id', $userId)
        ->where('object_id', $comment->id)
        ->where('object_type', PostComment::class)
        ->first();

    if ($reaction) {
        $hasReaction = false;
        $reaction->delete();
    } else {
        $hasReaction = true;
        Reaction::create([
            'object_id' => $comment->id,
            'object_type' => PostComment::class,
            'user_id' => $userId,
            'type' => $data['reaction']
        ]);
        if (!$comment->isOwner($userId)) {
            $user = User::where('id', $userId)->first();
            $comment->user->notify(new ReactionAddedOnComment($comment->post, $comment, $user));
        }

    }

    $reactions = Reaction::where('object_id', $comment->id)->where('object_type', PostComment::class)->count();

    return response([
        'num_of_reactions' => $reactions,
        'current_user_has_reaction' => $hasReaction
    ]);
}

public function aiPostContent(Request $request)
{
    $prompt = $request->get('prompt');

    $result = OpenAI::chat()->create([
        'model' => 'gpt-4',
        'messages' => [
            [
                'role' => 'user',
                'content' => "Please generate social media post content based on the following prompt. Generated formatted content with multiple paragraphs. Put hashtags after 2 lines from the main content" . PHP_EOL . PHP_EOL . "Prompt: " . PHP_EOL
                    . $prompt
            ],
        ],
    ]);

    return response([
        'content' => $result->choices[0]->message->content
    ]);
}
public function fetchUrlPreview(Request $request)
{
    $data = $request->validate([
        'url' => 'url'
    ]);
    $url = $data['url'];

    $html = file_get_contents($url);

    $dom = new \DOMDocument();

    // Suppress warnings for malformed HTML
    libxml_use_internal_errors(true);

    // Load HTML content into the DOMDocument
    $dom->loadHTML($html);

    // Suppress warnings for malformed HTML
    libxml_use_internal_errors(false);

    $ogTags = [];
    $metaTags = $dom->getElementsByTagName('meta');
    foreach ($metaTags as $tag) {
        $property = $tag->getAttribute('property');
        if (str_starts_with($property, 'og:')) {
            $ogTags[$property] = $tag->getAttribute('content');
        }
    }

    return $ogTags;
}

}
