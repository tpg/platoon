<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;
use TPG\Platoon\Contracts\PlatoonContract;
use TPG\Platoon\Target;

class TargetsCommand extends Command
{
    protected $signature = 'platoon:targets';

    protected $description = 'Output the available targets';

    public function handle(PlatoonContract $platoon): int
    {
        $default = $platoon->defaultTarget();

        $this->table([
            'Name',
            'Host',
            'Port',
            'Username',
            'Path',
        ], $platoon->targets()->map(fn(Target $target) => [
            $target->name.($default->name === $target->name ? ' *' : ''),
            $target->host,
            $target->port,
            $target->username,
            $target->path,
        ]));

        return self::SUCCESS;
    }
}
