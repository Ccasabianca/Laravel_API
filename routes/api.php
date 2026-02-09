<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
    ]);
});

Route::apiResource('books', BookController::class);
