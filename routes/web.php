<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\BroadcastController;


Route::get('/', function () {
    return view('welcome');
});

Route::match(['get','post'], '/botman', [BotController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


Route::get('/bot-test', function () {
    return view('bot');
});

Route::post('/webhook/teams', [TeamsController::class, 'handle']);
Route::get('/webhook/whatsapp', [WhatsAppController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'handle']);
Route::post('/api/broadcast', [BroadcastController::class, 'send']);


