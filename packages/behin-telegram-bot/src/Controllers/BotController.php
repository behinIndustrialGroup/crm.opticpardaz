<?php

namespace TelegramBot\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Mkhodroo\AltfuelTicket\Controllers\LangflowController;

class BotController extends Controller
{
    public function chat()
    {
        Log::info("Receive Message");
        $content = file_get_contents('php://input');
        $update = json_decode($content, true);
        $chat_id = $update['message']['chat']['id'];
        $text = $update['message']['text'];
        // switch ($text) {
        //     case "/start":
        //         $sentMsg = 'Hi';
        //         break;
        //     case "/command1":
        //         $sentMsg = 'Helllo';
        //         break;
        //     default:
        //         $sentMsg = 'دستور درست را انتخاب کنید';
        // }

        // $sentMsg = LangflowController::run($text);

        $telegram = new TelegramController(config('telegram_bot_config.TOKEN'));
        $telegram->sendMessage(
            array(
                'chat_id' => $chat_id,
                'text' => $chat_id
            )
        );


        // $return = file_get_contents($result);

    }

    public static function sendMessage($chat_id, $text)
    {
        $telegram = new TelegramController(config('telegram_bot_config.TOKEN'));
        $telegram->sendMessage(
            array(
                'chat_id' => $chat_id,
                'text' => $text
            )
        );
    }
}
