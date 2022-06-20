<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CleanupCommand extends Command
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
            $this->output->write('Removing release '.$base.'... ');
            $process = Process::fromShellCommandline('rm -rf '.$release);
            $process->run(function ($type, $output) {
                if ($type === Process::ERR) {
                    $this->output->writeln('ERROR!');
                    $this->error($output);

                    throw new \RuntimeException('Failed to complete cleanup');
                }

                $this->output->writeln('OK');
            });

        }

        return self::SUCCESS;
    }

    protected function getPath(): string
    {
        return Str::before(realpath(__DIR__), 'releases').'releases';
    }
}
