<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use TPG\Platoon\Contracts\PlatoonContract;

class ReleasesListCommand extends PlatoonCommand
{
    protected $signature = 'platoon:releases:list {target?}';

    protected $description = 'List all the current releases';

    public function handle(): int
    {
        if ($this->argument('target')) {
            return $this->runOnTarget('releases:list');
        }
        $releases = collect($this->platoon->releases());
        $active = $this->platoon->activeRelease();

        $rows = $releases->map(fn($release) => [
            $release,
            Carbon::createFromFormat('YmdHis', $release),
            $release => $active === $release ? '<fg=green>✓</>' : '',
        ]);

        $this->table(['Release ID', 'Date', 'Active'], $rows);
        return self::SUCCESS;
    }
}
