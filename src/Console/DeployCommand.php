<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Symfony\Component\Process\Process;

class DeployCommand extends PlatoonCommand
{
    protected $signature = 'platoon:deploy {target?}';

    protected $description = 'Run the deployment script';

    public function handle(): int
    {
        $target = $this->argument('target') ? $this->platoon->target($this->argument('target')) : $this->platoon->defaultTarget();
        $command = $this->platoon->getEnvoyCommand($target->name, 'deploy');
        $process = Process::fromShellCommandline($command, base_path(), timeout: 0);

        $process->setTty(Process::isTtySupported());

        $process->mustRun(function ($type, $buffer) {

            if ($type === Process::ERR) {
                return self::FAILURE;
            }

            $this->info($buffer);

            return self::SUCCESS;
        });

        return self::SUCCESS;
    }}
