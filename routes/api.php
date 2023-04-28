<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/posts/{id}',[PostController::class,'show']);
    Route::get('/me',[AuthenticationController::class,'me']);
    
    Route::post('/logout',[AuthenticationController::class,'logout']);

    //CRUD Post
    Route::post('/posts',[PostController::class,'store']);
    Route::patch('/posts/{id}',[PostController::class,'update'])->middleware('pemilik-postingan');
    Route::delete('/posts/{id}',[PostController::class,'destroy'])->middleware('pemilik-postingan');
});

Route::get('/posts',[PostController::class,'index']);

Route::post('/login',[AuthenticationController::class,'login']);


