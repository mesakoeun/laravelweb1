<?php

//use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'index']);
Route::get('/products', [ProductController::class, 'list']);
Route::post('/save', [ProductController::class, 'save']);
Route::post('/delete', [ProductController::class, 'delete']);
