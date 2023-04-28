<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostDetailResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        //return response()->json(['data' => $posts]);

        return PostDetailResource::collection($posts->loadMissing('writer:id,username'));
        
    }

    public function show($id)
    {
        $post = Post::with('writer:id,username')->findOrFail($id);
        
        //return response()->json(['data' => $post]); 
        return new PostDetailResource($post);
    }

    public function store(Request $request)
    {
        //dd(Auth::user());
        $validated = $request->validate([
            'atas_nama' => 'required|max:100',
            'keterangan' => 'required',
            'tanggal_pembuatan' => 'required'
            
        ]);

        $document = null;
        if ($request->file) {
            $filename = $this->generateRandomString();
            $extension = $request->file->extension();
            $document = $filename.'.'.$extension;

            Storage::putFileAs('document', $request->file, $document);
        }

        $request['document'] = $document;
        $request['user_id'] = Auth::user()->id;
        $post = Post::create($request->all());
        return new PostDetailResource($post->loadMissing('writer:id,username'));
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'atas_nama' => 'required|max:100',
            'keterangan' => 'required',
            'tanggal_pembuatan' => 'required'
        ]);

        $post = Post::findOrFail($id);
        $post->update($request->all());

        return new PostDetailResource($post->loadMissing('writer:id,username'));
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return new PostDetailResource($post->loadMissing('writer:id,username'));
    }


    function generateRandomString($length = 30) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
