<?php

namespace App\Http\Controllers;

use App\Blog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /*
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getRegisteredUsers()
    {
        $users = User::orderBy('id', 'DESC')->get();
        return view('users', ['users' => $users]);
    }

    /**
     * Show all blog posts
     */
    public function PostList()
    {
        $posts = Blog::with('writer')->get();
        return view('post_list', ['posts' => $posts]);
    }

    /**
     * Form to create post
     */
    public function createPost()
    {
        return view('post_create');
    }

    /**
     * Store post
     */
    public function storePost (Request $request)
    {
        $request->validate([
            'title' => 'required',
            'body' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]
        );

        $image = $request->file('image');
        $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/images');
        $image->move($destinationPath, $input['imagename']);

        $article = new Blog();
        $article->title = $request->get('title');
        $article->body = $request->get('body');
        $article->author = Auth::id();
        $article->image = $input['imagename'];
        $article->save();
        return redirect()->route('all_posts')->with('status', 'New article has been successfully created!');
    }

    /**
     * Edit post
     */
    public function editPost($post_id)
    {
        $post = Blog::find($post_id);
        return view('edit_form', ['post' => $post]);
    }

    public function updatePost(Request $request, $post_id)
    {
        $post = Blog::find($post_id);
        $post->title = $request->get('title');
        $post->body = $request->get('body');
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $input['imagename'] = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $input['imagename']);
            $post->image = $input['imagename'];
        }
        $post->save();
        return redirect()->route('all_posts')->with('status', 'Post has been successfully updated!');
    }

}
