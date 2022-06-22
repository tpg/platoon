<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FinishCommand extends Command
{
    protected $signature = 'platoon:finish';

    protected $description = 'Finish up after a deployment';

    public function handle(): int
    {
        $this->resetOpcache();

        return self::SUCCESS;
    }

    protected function resetOpcache(): void
    {
        $this->info('Resetting OPCache');
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
}
