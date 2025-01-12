<?php

namespace BaleBot\Controllers;

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
        // $url = "https://tapi.bale.ai/bot" . config('telgram_bot_config.TOKEN') . "/sendmessage";
        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL =>  $url . '?chat_id=' . $chat_id . '&text=' . $sentMsg,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        // ));

        // $response = curl_exec($curl);
        // $er = curl_error($curl);
        // Log::info($er);
        // curl_close($curl);

        $sentMsg = LangflowController::run($text);

        $telegram = new TelegramController(config('bale_bot_config.TOKEN'));
        $telegram->sendMessage(
            array(
                'chat_id' => $chat_id,
                'text' => $sentMsg
            )
        );


        // $return = file_get_contents($result);

    }
}
