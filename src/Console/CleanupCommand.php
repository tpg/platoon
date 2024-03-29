<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CleanupCommand extends PlatoonCommand
{
    protected $signature = 'platoon:cleanup {--k|keep=2 : Number if deployments to keep installed}';

    protected $description = 'Clean up old releases.';

    public function handle(): int
    {
        $path = $this->getPath();

        $this->info('Cleanup of deployments from '.$path);

        $releases = glob($path.'/*');

        if (count($releases) < $this->option('keep') + 1) {
            return 0;
        }

        foreach (array_slice($releases, 0, count($releases) - $this->option('keep')) as $release) {

            $base = basename($release);
            $this->output->writeln('Removing release '.$base.'... ');
            $process = Process::fromShellCommandline('rm -rf '.$release, timeout: 0);
            $process->mustRun(function ($type, $output) {
                if ($type === Process::ERR) {
                    $this->output->writeln('ERROR!');
                    $this->error($output);

                    throw new \RuntimeException('Failed to complete cleanup');
                }

            });

        }

        return self::SUCCESS;
    }

    protected function getPath(): string
    {
        return Str::before(realpath(base_path()), 'releases').'releases';
    }
}
