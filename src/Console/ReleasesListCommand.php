<?php

declare(strict_types=1);

namespace TPG\Platoon\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReleasesListCommand extends Command
{
    protected $signature = 'platoon:releases:list';

    protected $description = 'List all the current releases';

    public function handle(): int
    {
        $releases = collect(
            File::glob(Str::before(__DIR__, 'releases').'releases/*')
        )->map(fn($path) => Str::afterLast($path, '/'));

        $rows = $releases->map(fn($release) => [
            $release,
            Carbon::createFromFormat('YmdHis', $release),
            $release => $this->activeRelease() ? '*' : '',
        ]);

        $this->table(['Release ID', 'Date', 'Active'], $rows);
        return self::SUCCESS;
    }

    protected function activeRelease(): string
    {
        $path = Str::before(__DIR__, 'releases');
        $link = readlink($path.'/live');

        return Str::afterLast($link, '/');
    }
}
