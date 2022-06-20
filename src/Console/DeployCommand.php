<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DeployCommand extends Command
{
    protected $signature = 'platoon:deploy {server?}';

    protected $description = 'Run the deployment script';

    public function handle(): int
    {
        $exec = base_path('vendor/bin/envoy');
        $script = 'vendor/thepublicgood/platoon/scripts/deploy.blade.php';

        $process = Process::fromShellCommandline($this->getCommand(), base_path());

        $process->setTty(Process::isTtySupported());

        $process->mustRun(function ($type, $buffer) {

            if ($type === Process::ERR) {
                return self::FAILURE;
            }

            $this->info($buffer);

            return self::SUCCESS;
        });

        return self::SUCCESS;
    }

    protected function getCommand(): string
    {
        $exec = base_path('vendor/bin/envoy');
        $script = 'vendor/thepublicgood/platoon/scripts/deploy.blade.php';

        return $exec.' run deploy --conf='.$script.' --server='.$this->argument('server');
    }
}
