<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\ServiceProvider;
use TPG\Platoon\Console\CleanupCommand;
use TPG\Platoon\Console\PublishCommand;
use TPG\Platoon\Contracts\PlatoonContract;

class PlatoonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/platoon.php' => config_path('platoon.php'),
            __DIR__.'/../scripts/deploy.blade.php' => base_path('Envoy.blade.php'),
        ], 'platoon');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupCommand::class,
                PublishCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/platoon.php', 'platoon');

        $this->app->bind(PlatoonContract::class, Platoon::class);
    }
}
