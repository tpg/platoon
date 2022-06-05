<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;

class Target extends Data
{
    public readonly string $host;
    public readonly int $port;
    public readonly string $username;
    public readonly string $path;
    public readonly string $php;
    public readonly string $branch;

    public readonly string $hostString;

    public function __construct(array $config)
    {
        $this->host = Arr::get($config, 'host');
        $this->port = Arr::get($config, 'port', 22);
        $this->username = Arr::get($config, 'username');
        $this->path = Arr::get($config, 'path');
        $this->php = Arr::get($config, 'php', 'php');
        $this->branch = Arr::get($config, 'branch', 'main');

        $this->hostString = $this->getHostString();
    }

    protected function getHostString(): string
    {
        return $this->username.'@'.$this->host.' -p'.$this->port;
    }
}
