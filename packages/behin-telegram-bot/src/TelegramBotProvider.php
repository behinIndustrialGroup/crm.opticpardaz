<?php

namespace TelegramBot;

use Illuminate\Support\ServiceProvider;

class TelegramBotProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/telegram_bot_config.php' => config_path('telegram_bot_config.php')
        ]);
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
