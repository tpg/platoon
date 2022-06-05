<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;

class CleanupCommand extends Command
{
    protected $signature = 'platoon:cleanup {--keep|k= : Number if deployments to keep installed}';
}
