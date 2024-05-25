<?php

namespace BehinInit;

use Illuminate\Support\ServiceProvider;

class BehinInitProvider extends ServiceProvider
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
            __DIR__.'/resources/layouts' => resource_path('views/behin-layouts'),
            __DIR__. '/public' => public_path('behin')
        ]);
    }
}
