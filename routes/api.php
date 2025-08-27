<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;


https://phplaravel-1483035-5810347.cloudwaysapps.com/


Route::get('categories', [CategoryController::class, 'index']);


