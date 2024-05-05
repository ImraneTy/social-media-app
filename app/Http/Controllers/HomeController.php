<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;

class HomeController extends Controller
{

    public function index(Request $request){
    $userId = Auth::id();

        $posts=Post::query()
        ->withCount('reactions')
        ->withCount('comments')
        ->with(['comments','reactions'=>function($query)use($userId){
            $query->where('user_id',$userId);
        }])
        ->latest()
        ->paginate(20);
        return Inertia::render('Home',['posts'=>PostResource::collection($posts)]);
    }
}
