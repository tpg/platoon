<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\VendorPublishCommand;
use TPG\Platoon\PlatoonServiceProvider;

class PublishCommand extends Command
{
    protected $signature = 'platoon:publish {--force} {--script}';

    protected $description = 'Publish the platoon config';

    public function handle(): int
    {
        $this->call(VendorPublishCommand::class, [
            '--provider' => PlatoonServiceProvider::class,
            '--tag' => 'platoon-config',
            '--force' => $this->option('force'),
        ]);

        return self::SUCCESS;
    }
}
