<?php


namespace Behin\SimpleWorkflow\Jobs;

use App\Models\User;
use Behin\SimpleWorkflow\Controllers\Core\PushNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $pushUserId;
    protected $title;
    protected $message;
    protected $icon;

    public function __construct($userId, $title = "کارجدید", $message = "کار جدید بهتون ارجاع داده شد")
    {
        $this->userId = $userId;
        $this->pushUserId = config('broadcasting.pusher.prefix_user') . $userId;
        $this->title = $title;
        $this->message = $message;
        $this->icon = url('public/behin/logo.ico');
    }

    public function handle()
    {
        $beamsClient = new PushNotifications();
        $user = User::find($this->userId);
        $title = $user->name . 'عزیز' ?? $this->title;
        $beamsClient->publishToUsers(
            [$this->pushUserId],
            [
                "web" => [
                    "notification" => [
                        "title" => $title,
                        "body" => $this->message,
                        "icon" => $this->icon
                    ]
                ]
            ]
        );
    }
}