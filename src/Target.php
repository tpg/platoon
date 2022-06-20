<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;

class Target extends Data
{
    protected array $config;

    public readonly string $name;
    public readonly string $host;
    public readonly int $port;
    public readonly string $username;
    public readonly string $path;
    public readonly string $php;
    public readonly string $composer;
    public readonly string $branch;
    public readonly bool $migrate;

    protected array $paths = [
        'releases' => 'releases',
        'serve' => 'live',
        'storage' => 'storage',
    ];

    public readonly string $hostString;

    public function __construct(string $name, array $config)
    {
        $this->config = $config;

        $this->name = $name;
        $this->host = Arr::get($config, 'host');
        $this->port = Arr::get($config, 'port', 22);
        $this->username = Arr::get($config, 'username');
        $this->path = Arr::get($config, 'path');
        $this->php = Arr::get($config, 'php', 'php');
        $this->composer = Arr::get($config, 'composer', 'composer');
        $this->branch = Arr::get($config, 'branch', 'main');
        $this->migrate = Arr::get($config, 'migrate', false);

        $this->hostString = $this->getHostString();
    }

    protected function getHostString(): string
    {
        return $this->username.'@'.$this->host.' -p'.$this->port;
    }

    public function paths(string $pathName, string $suffix = null): string
    {
        if (! Arr::has($this->paths, $pathName)) {
            throw new \RuntimeException('No defined path named '.$pathName);
        }

        $parts = collect([
            $this->path,
            $this->paths[$pathName],
            $suffix,
        ]);

        return implode('/', $parts->whereNotNull()->toArray());
    }

    public function composer(): string
    {
        if (! str_contains($this->composer, '/')) {
            return $this->composer;
        }

        return $this->php.' '.$this->composer;
    }

    public function artisan(bool $fullPath = false): string
    {
        if (! $fullPath) {
            return $this->php.' ./artisan';
        }

        return $this->php.' '.$this->paths('serve').'/artisan';
    }

    public function assets(): array
    {
        return Arr::get($this->config, 'assets', []) ?? [];
    }
}
