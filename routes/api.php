<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\LikeController;



// https://phplaravel-1483035-5810347.cloudwaysapps.com

Route::get('categories', [CategoryController::class, 'index']);
Route::post('categories', [CategoryController::class, 'store']);
Route::put('categories/{category}', [CategoryController::class, 'update']);
Route::delete('categories/{category}', [CategoryController::class, 'destroy']);


Route::get('posts', [PostController::class, 'index']);
Route::post('posts', [PostController::class, 'store']);
Route::put('posts/{category}', [PostController::class, 'update']);
Route::delete('posts/{category}', [PostController::class, 'destroy']);

Route::get('parts', [PartController::class, 'index']);
Route::post('parts', [PartController::class, 'store']);
Route::put('parts/{category}', [PartController::class, 'update']);
Route::delete('parts/{category}', [PartController::class, 'destroy']);


Route::get('likes', [LikeController::class, 'index']);
Route::post('likes', [LikeController::class, 'store']);
