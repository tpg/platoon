<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Foundation\Console\VendorPublishCommand;
use TPG\Platoon\PlatoonServiceProvider;

class PublishCommand extends PlatoonCommand
{
    protected $signature = 'platoon:publish {--force} {--envoy}';

    protected $description = 'Publish the platoon config';

    public function handle(): int
    {
        $this->call(VendorPublishCommand::class, [
            '--provider' => PlatoonServiceProvider::class,
            '--tag' => 'platoon-config',
            '--force' => $this->option('force'),
        ]);

        if ($this->option('envoy')) {
            $this->call(VendorPublishCommand::class, [
                '--provider' => PlatoonServiceProvider::class,
                '--tag' => 'platoon-envoy',
                '--force' => $this->option('force'),
            ]);
        }

        return self::SUCCESS;
    }
}
