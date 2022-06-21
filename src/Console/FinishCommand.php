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
        if (app()->environment('local')) {
            $this->error('This command is only meant for production.');
            return self::FAILURE;
        }

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
