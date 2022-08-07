<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command as IlluminateCommand;
use Symfony\Component\Process\Process;
use TPG\Platoon\Contracts\PlatoonContract;

abstract class PlatoonCommand extends IlluminateCommand
{
    protected PlatoonContract $platoon;

    protected function configure(): void
    {
        $this->platoon = app(PlatoonContract::class);
    }

    protected function runOnTarget(string $command, array $options = []): int
    {
        $this->platoon->runEnvoy($this->argument('target'), $command, $options, function ($type, $buffer) {
            if ($type === Process::ERR) {
                return self::FAILURE;
            }

            $this->info($buffer);

            return self::SUCCESS;
        });

        return self::SUCCESS;
    }
}
