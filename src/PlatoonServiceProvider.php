<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\ServiceProvider;

class PlatoonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/platoon.php' => config_path('platoon.php')
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([

            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/platoon.php', 'platoon');
    }
}
