<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use TPG\Platoon\Contracts\TargetContract;

class Target implements TargetContract
{
    /**
     * @var string
     */
    public readonly string $hostString;

    /**
     * @var array<string>
     */
    protected readonly array $extra;

    public function __construct(
        public readonly string $name,
        protected readonly array $config,
        protected readonly ?string $release = null
    )
    {
        $this->hostString = $this->getHostString();
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }


    /**
     * Get the complete host connection string
     *
     * @return string
     */
    protected function getHostString(): string
    {
        $username = $this->config('username');
        $host = $this->config('host');
        $port = $this->config('port');

        $parts = [
            $username ? $username.'@' : null,
            $host,
            $port ? ' -p'.$port : '',
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
        $paths = $this->config('paths');

        if (! Arr::has($paths, $pathName)) {
            throw new \RuntimeException('No defined path named '.$pathName);
        }

        $parts = collect([
            $this->config('root'),
            Arr::get($paths, $pathName),
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
        if (! $this->config('composer')) {
            return $this->config('php').' composer.phar';
        }

        if (! str_contains($this->config('composer'), '/')) {
            return $this->config('composer');
        }

        return $this->config('php').' '.$this->config('composer');
    }

    /**
     * Get a string of flags to pass to the Composer CLI.
     *
     * @return string
     */
    public function composerFlags(): string
    {
        return implode(' ', [
            '--no-progress',
            ...$this->config('extra.composer-flags', [
                '--no-dev',
                '--optimize-autoloader',
            ]),
        ]);
    }

    /**
     * Get the fully qualified path to the Artisan executable.
     *
     * @return string
     */
    public function artisan(): string
    {
        return $this->config('php').' '.$this->paths('serve').'/artisan';
    }

    /**
     * Get the fully-qualified path to the specified project path.
     *
     * @param  string  $release
     * @return array
     */
    public function assets(string $release): array
    {
        $path = $this->config('username').'@'.$this->config('host');

        return collect($this->config('assets'))->mapWithKeys(
            fn($dest, $source) => [$source => $path.':'.$this->paths('releases', $release).'/'.$dest]
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
        $hooks = $this->config('hooks');

        return array_map(
            static fn (string $command) => $expander->expand($command), Arr::get($hooks, $step, []) ?? []
        );
    }

    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return $this->config($name);
    }
}
