<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Web\WebDriver;

class BotController extends Controller
{
    public function handle(Request $request)
    {
        DriverManager::loadDriver(WebDriver::class);

        $config = [];

        // IMPORTANT — Pass request object
        $botman = BotManFactory::create($config, null, $request);

        $botman->hears('.*', function (BotMan $bot) {
            $bot->reply('RAW INPUT: ' . json_encode(request()->all()));
        });


        $botman->listen();
    }
}

