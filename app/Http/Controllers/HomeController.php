<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Group;
use App\Http\Enums\GroupUserStatus;
use App\Http\Resources\GroupResource;

use Inertia\Inertia;

class HomeController extends Controller
{

    public function index(Request $request){
    $userId = Auth::id();

        $posts=Post::query()
        ->withCount('reactions')
        // ->withCount('comments')
        ->with([
            'comments' => function ($query) use($userId) {
            $query
            // ->whereNull('parent_id')
            ->withCount('reactions')
            ;
        },'reactions'=>function($query)use($userId){
            $query->where('user_id',$userId);
        }])
        ->latest()
        ->paginate(10);
        $posts = PostResource::collection($posts);

        if ($request->wantsJson()) {
            return $posts;
        }


        $groups = Group::query()
        ->with('currentUserGroup')
        ->select(['groups.*'])
        ->join('group_users AS gu', 'gu.group_id', 'groups.id')
        ->where('gu.user_id', Auth::id())
        // ->where('status', GroupUserStatus ::APPROVED->value)
        ->orderBy('gu.role')
        ->orderBy('name', 'desc')
        ->get();

        return Inertia::render('Home', [
            'posts' => $posts,
            'groups' => GroupResource::collection($groups),
        ]);
    }
}
