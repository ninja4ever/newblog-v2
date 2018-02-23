<?php

namespace App\Http\Controllers;

use App\Http\Helper\UploadHelper;
use Illuminate\Http\Request;
use App\Post;
use App\PostCategory;
use Validator;
use Illuminate\Support\Str;
use File;

class PostController extends Controller
{
    // messages used in validate
    private $messages = [
        'title.required' => 'Pole tytułu jest wymagane',
        'title.unique' => 'Tytuł postu jest już w bazie',
        'title.max' => 'Tytuł nie może przkroczyć :max znaków',
        'body.required' => 'Pole wymagane',
        'slug.required' => 'Pole linku jest wymagane',
        'slug.unique' => 'Pole linku musi byc unikalne',
        'slug.max' => 'Pole linku nie może przkroczyć :max znaków',
        'active.required' => 'Zaznaczenie pola jest wymagane',
        'category.required' => 'Zaznaczenie pola kategorii jest wymagane',
        'image.required' => 'Obrazek obiektu jest wymagany',
        'image.mimes' => 'Dozwolone są tylko pliki jpeg, jpg, png',
        'image.max' => 'Maksymalny rozmiar pliku to :max kb',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     * show post listing
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $post = Post::with('postcategory')->with('user')->where('user_id', \Auth::User()->id)->orderBy('created_at', 'desc')->get();
        return view('admin.posts.index', ['posts' => $post]);
    }

    /**
     * Show the form for creating a new resource.
     * show create post form
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $cposts = PostCategory::all();
        return view('admin.posts.add', compact('cposts'));
    }

    /**
     * Store a newly created resource in storage.
     * add post
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $image = $request->image;
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
            'slug' => 'required|unique:posts|max:255',
            'active' => 'required|in:0,1',
            'category' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1500',
        ], $this->messages);
        if ($validator->fails()) {
            \Session::flash('alert-warning', trans('messages.post_add_message_warning'));
            return redirect('/post/add')
                ->withInput()
                ->withErrors($validator);
        }
        $fileName = '';
        if ($image) {
            $fileName = UploadHelper::uploadFiles($image, UploadHelper::POST_IMAGE);
        }

        Post::create([
            'title' => $request->title,
            'body' => $request->body,
            'slug' => str_slug($request->slug, '-'),
            'category' => $request->category,
            'excerpt' => Str::words($request->body),
            'active' => $request->active,
            'image' => $fileName,
            'user_id' => \Auth::User()->id,
        ]);
        \Session::flash('alert-success', trans('messages.post_add_message_success'));
        return redirect('/posts');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * show post
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $cposts = PostCategory::all();
        $post = Post::find($post->id);
        return view('admin.posts.edit', compact('cposts', 'post'));
    }

    /**
     * Update the specified resource in storage.
     * update post
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $post = Post::find($id);
        $image = $request->image;

        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:posts,id,' . $post->id . '|max:255',
            'body' => 'required',
            'slug' => 'required|unique:posts,id,' . $post->id . '|max:255',
            'active' => 'required|in:0,1',
            'category' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:1500',
        ], $this->messages);
        if ($validator->fails()) {
            \Session::flash('alert-warning', trans('messages.post_add_message_warning'));
            return redirect('/post/edit/' . $id)
                ->withInput()
                ->withErrors($validator);
        }
        if ($image) {
            $post->image = UploadHelper::uploadFiles($image, UploadHelper::POST_IMAGE, true, $post->image);
        }

        $post->title = $request->title;
        $post->body = $request->body;
        $post->updated_at = date('Y-m-d H:i:s');
        $post->slug = str_slug($request->slug, '-');
        $post->category = $request->category;
        $post->excerpt = Str::words($request->body);
        $post->active = $request->active;

        $post->update();

        \Session::flash('alert-success', trans('messages.post_update_message_success_update'));
        return redirect('/posts');
    }

    /**
     * Remove the specified resource from storage.
     * remove post
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Post $post)
    {
        UploadHelper::deleteFile($post->image, UploadHelper::POST_IMAGE);
        $post->delete();
        \Session::flash('alert-success', trans('messages.post_message_delete'));
        return redirect('/posts');
    }

    /**
     * publish the specified resource from storage.
     * publish post
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function publicPost(Request $request, Post $post)
    {
        $post->active = $request->input('active');
        $post->updated_at = date('Y-m-d H:i:s');
        $post->update();
        \Session::flash('alert-success', trans('messages.post_update_message_success'));
        return redirect('/posts');
    }

}
