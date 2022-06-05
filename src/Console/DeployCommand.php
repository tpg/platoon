<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;

class DeployCommand extends Command
{
    protected $signature = 'platoon:deploy {--target=}';

    protected $description = 'Deploy to the specified target';
}
