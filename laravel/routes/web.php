<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FollowController;

Route::get('/users', [UserController::class, 'getUsers']);
Route::get('/users/{id}', [UserController::class, 'getUserItem'])->whereNumber('id');
Route::post('/users', [UserController::class, 'createUser']);
Route::patch('/users/{id}', [UserController::class, 'updateUser'])->whereNumber('id');
Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->whereNumber('id');

Route::get('/posts', [PostController::class, 'getPosts']);
Route::get('/posts/{id}', [PostController::class, 'getPostItem'])->whereNumber('id');
Route::post('/posts', [PostController::class, 'createPost']);
Route::patch('/posts/{id}', [PostController::class, 'updatePost'])->whereNumber('id');
Route::delete('/posts/{id}', [PostController::class, 'deletePost'])->whereNumber('id');

Route::get('/comments', [CommentController::class, 'getComments']);
Route::get('/comments/{id}', [CommentController::class, 'getCommentItem'])->whereNumber('id');
Route::post('/comments', [CommentController::class, 'createComment']);
Route::patch('/comments/{id}', [CommentController::class, 'updateComment'])->whereNumber('id');
Route::delete('/comments/{id}', [CommentController::class, 'deleteComment'])->whereNumber('id');

Route::get('/likes', [LikeController::class, 'getLikes']);
Route::get('/likes/{id}', [LikeController::class, 'getLikeItem'])->whereNumber('id');
Route::post('/likes', [LikeController::class, 'createLike']);
Route::patch('/likes/{id}', [LikeController::class, 'updateLike'])->whereNumber('id');
Route::delete('/likes/{id}', [LikeController::class, 'deleteLike'])->whereNumber('id');

Route::get('/follows', [FollowController::class, 'getFollows']);
Route::get('/follows/{id}', [FollowController::class, 'getFollowItem'])->whereNumber('id');
Route::post('/follows', [FollowController::class, 'createFollow']);
Route::patch('/follows/{id}', [FollowController::class, 'updateFollow'])->whereNumber('id');
Route::delete('/follows/{id}', [FollowController::class, 'deleteFollow'])->whereNumber('id');
