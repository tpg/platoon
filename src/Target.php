<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use TPG\Platoon\Contracts\TargetContract;

class Target implements TargetContract
{
    protected array $config;

    /**
     * @var string
     */
    public readonly string $name;

    /**
     * @var string
     */
    public readonly string $host;

    /**
     * @var int
     */
    public readonly ?int $port;

    /**
     * @var string|null
     */
    public readonly ?string $username;

    /**
     * @var string
     */
    public readonly string $path;

    /**
     * @var string
     */
    public readonly string $php;

    /**
     * @var string
     */
    public readonly string $composer;

    /**
     * @var string
     */
    public readonly string $branch;

    /**
     * @var bool
     */
    public readonly bool $migrate;

    /**
     * @var array<string, string>
     */
    public readonly array $assets;

    /**
     * @var array<string, string>
     */
    protected readonly array $paths;

    /**
     * @var string
     */
    public readonly string $hostString;

    public function __construct(string $name, array $config)
    {
        $this->config = $config;

        $this->name = $name;
        $this->host = Arr::get($config, 'host');
        $this->port = Arr::get($config, 'port');
        $this->username = Arr::get($config, 'username', null);
        $this->path = Arr::get($config, 'path');
        $this->php = Arr::get($config, 'php', '/usr/bin/php');
        $this->composer = Arr::get($config, 'composer', $this->path.'/composer.phar');
        $this->branch = Arr::get($config, 'branch', 'main');
        $this->migrate = Arr::get($config, 'migrate', false);
        $this->assets = Arr::get($config, 'assets', []) ?? [];
        $this->paths = Arr::get($config, 'paths');

        $this->hostString = $this->getHostString();
    }

    /**
     * Get the complete host connection string
     *
     * @return string
     */
    protected function getHostString(): string
    {
        $parts = [
            $this->username ? $this->username.'@' : null,
            $this->host,
            $this->port ? ' -p'.$this->port : '',
        ];

        return implode('', $parts);
    }

    /**
     * Get the fully-qualified path for the specified path-name
     *
     * @param  string  $pathName
     * @param  string|null  $suffix
     * @return string
     */
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

    /**
     * Get the fully-qualified path to the composer binary.
     *
     * @return string
     */
    public function composer(): string
    {
        if (! str_contains($this->composer, '/')) {
            return $this->composer;
        }

        return $this->php.' '.$this->composer;
    }

    /**
     * Get the fully qualified path to the Artisan executable.
     *
     * @return string
     */
    public function artisan(): string
    {
        return $this->php.' '.$this->paths('serve').'/artisan';
    }

    /**
     * Get the fully-qualified path to the specified project path.
     *
     * @param  string  $release
     * @return array
     */
    public function assets(string $release): array
    {
        return collect($this->assets)->mapWithKeys(
            fn($dest, $source) => [$source => $this->username.'@'.$this->host.':'.$this->paths('releases', $release).'/'.$dest]
        )->toArray();
    }

    /**
     * Get an array of hook-in commands for the specified step.
     *
     * @param  string  $step
     * @return array<string>
     */
    public function hooks(string $step): array
    {
        $expander = new TagExpander($this);
        return array_map(fn (string $command) => $expander->expand($command), Arr::get($this->config, 'hooks.'.$step, []) ?? []);
    }
}
