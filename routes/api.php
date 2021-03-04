<?php

use App\Http\Controllers\Api\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
  return $request->user();
});

Route::get('/google_assistant', [TestController::class, 'google_assistant']);
Route::get('/token', [TestController::class, 'token']);
