<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use TPG\Platoon\Contracts\PlatoonContract;

class ReleasesSetCommand extends PlatoonCommand
{
    protected $signature = 'platoon:releases:set {release} {target?}';

    protected $description = 'Set the specified release as active';

    public function handle(): int
    {
        if ($this->argument('target')) {
            return $this->runOnTarget('releases:set', [
                '--set-release' => $this->argument('release')
            ]);
        }

        if (! $this->validRelease()) {
            $this->error('No release '.$this->getRelease());
            return self::INVALID;
        }

        $path = Str::before(base_path('..'), '/releases');

        if (file_exists($path.'/'.$this->platoon->paths('serve'))) {
            unlink($path.'/'.$this->platoon->paths('serve'));
        }

        try {
            symlink(
                $path.'/releases/'.$this->getRelease(),
                $path.'/'.$this->platoon->paths('serve')
            );
        } catch (\Exception $exception)
        {
            $this->error('Could not create symlink: '.$exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Release '.$this->getRelease().' is now active.');
        return self::SUCCESS;
    }

    protected function validRelease(): bool
    {
        $releases = $this->platoon->releases();

        return in_array($this->getRelease(), $releases, true);
    }

    protected function getRelease(): string
    {
        return match($this->argument('release')) {
            'latest' => $this->platoon->latestRelease(),
            default => $this->argument('release'),
        };
    }
}
