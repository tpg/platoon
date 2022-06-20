<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\VendorPublishCommand;
use TPG\Platoon\PlatoonServiceProvider;

class PublishCommand extends Command
{
    protected $signature = 'platoon:publish {--force}';

    protected $description = 'Publish the platoon config and deployment scripts';

    public function handle(): int
    {
        $this->call(VendorPublishCommand::class, [
            '--provider' => PlatoonServiceProvider::class,
            '--force' => $this->option('force'),
        ]);

        return self::SUCCESS;
    }
}
