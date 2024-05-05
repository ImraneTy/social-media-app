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
use App\Models\postReaction;
use App\Models\PostComment;
use App\Http\Resources\CommentResource;


class PostController extends Controller
{

 


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
        //todo
        $id = Auth::id();

        if($post->user_id !==$id){
            return response("you have no permition to delete this post",403);
        }
        $post->delete(); 
        return back();

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
        $reaction=PostReaction::where('user_id',$userId)->where('post_id',$post->id)->first();


        if($reaction){
            $hasReaction=false;
            $reaction->delete();
        }else{
            $hasReaction=true;
            PostReaction::create([
            'post_id'=>$post->id,
            'user_id'=>Auth::id(),
            'type'=>$data['reaction'],
        ]);  
        }


        $reactions=PostReaction::where('post_id',$post->id)->count();
        return response([
            'num_of_reactions' => $reactions,
            'current_user_has_reaction' => $hasReaction
        ]);
    }
    public function createComment(Request $request, Post $post)
    {
        $data = $request->validate([
            'comment' => ['required'],
        ]);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id(),
        ]);

        // $post = $comment->post;
        // $post->user->notify(new CommentCreated($comment, $post));

        return response(new CommentResource($comment), 201);

    }
}
