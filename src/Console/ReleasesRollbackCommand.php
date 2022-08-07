<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
class ReleasesRollbackCommand extends PlatoonCommand
{
    protected $signature = 'platoon:releases:rollback {target?}';

    protected $description = 'Roll back to the previous release';

    public function handle(): int
    {
        if ($this->argument('target')) {
            return $this->runOnTarget('releases:rollback');
        }

        $path = Str::before(base_path('..'), '/releases');
        $releases = $this->platoon->releases($path);
        $active = $this->platoon->activeRelease($path);

        if (! $active) {
            $this->error('No active release. Cannot rollback');
            return self::FAILURE;
        }

        $index = array_search($active, $releases, true);

        if (! $index) {
            $this->error('No release available for rollback');
            return self::INVALID;
        }

        if (file_exists($path.'/'.$this->platoon->paths('serve'))) {
            unlink($path.'/'.$this->platoon->paths('serve'));
        }


        $newRelease = $releases[$index -1];

        symlink($path.'/'.$this->platoon->paths('releases', $newRelease), $path.'/'.$this->platoon->paths('serve'));
        $this->info('Rollback completed. Release '.$newRelease.' is now live');

        return self::SUCCESS;
    }
}
