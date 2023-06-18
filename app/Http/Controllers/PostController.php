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
        //return $request->document;
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

        //dd($document);

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

    public function hapus($id)
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

    //mendapat kan file dari local storage
    public function download_local(Request $request){
        //dd("berhasil");
        if (Storage::disk('local')->exists("document/$request->file")) {
            $path = Storage::disk('local')->path("document/$request->file");
            $content=file_get_contents($path);
            return response($content)->withHeaders([
                "Content-Type"=>mime_content_type($path)
            ]);
        }
        return redirect('/404');
    }

    //filter tanggal date
    public function filter(Request $request)
    {   
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        //$data =  Post::whereDate('created_at','>=',$start_date)
        //                    ->whereDate('created_at','<=',$end_date)
        //                    ->get();
        if ($start_date == "") {
            $posts = Post::all();
            return PostDetailResource::collection($posts->loadMissing('writer:id,username'));
        }elseif ($end_date == "") {
            $posts = Post::all();
            return PostDetailResource::collection($posts->loadMissing('writer:id,username'));
        }
        else{
        $data =  Post::whereDate('tanggal_pembuatan','>=',$start_date)
                            ->whereDate('tanggal_pembuatan','<=',$end_date)
                            ->get();

        return PostDetailResource::collection($data->loadMissing('writer:id,username'));
    
        }
    }
}
