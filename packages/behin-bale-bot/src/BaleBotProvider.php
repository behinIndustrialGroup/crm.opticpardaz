<?php

namespace BaleBot;

use Illuminate\Support\ServiceProvider;

class BaleBotProvider extends ServiceProvider
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
            __DIR__ . '/config/bale_bot_config.php' => config_path('bale_bot_config.php')
        ]);
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
