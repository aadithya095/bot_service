<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;


Route::get('/', function () {
    return view('welcome');
});

Route::match(['get','post'], '/botman', [BotController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
