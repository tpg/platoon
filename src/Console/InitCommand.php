<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;

class InitCommand extends Command
{
    protected $signature = 'platoon:init';

    protected $description = 'Initialize a new deployment config';

    public function handle(): int
    {
        return 0;
    }
}
