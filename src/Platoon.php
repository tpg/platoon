<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use TPG\Platoon\Contracts\PlatoonContract;

class Platoon implements PlatoonContract
{
    protected array $config;

    public function __construct(array $config = null)
    {
        $this->config = $config ?? config('platoon');
    }

    /**
     * @return Collection<Target>
     */
    public function targets(): Collection
    {
        $targets = collect(array_keys(Arr::except(Arr::get($this->config, 'targets'), 'common')));

        return $targets->mapWithKeys(fn($name) => [$name => $this->target($name)]);
    }

    /**
     * @param  string  $name
     * @param  string|null  $release
     * @return Target
     */
    public function target(string $name, ?string $release = null): Target
    {
        $data = array_merge($this->common(), Arr::get($this->config, 'targets.'.$name));

        return new Target($name, [
            ...$data,
            'paths' => $this->paths(),
        ], $release);
    }

    /**
     * @return Target
     */
    public function defaultTarget(): Target
    {
        $defaultName = Arr::get($this->config, 'default');

        if ($defaultName && ! $this->targets()->has($defaultName)) {
            throw new \RuntimeException('No target with name "'.$defaultName.'"');
        }

        if (! $defaultName) {
            return $this->targets()->first();
        }

        return $this->targets()->get($defaultName);
    }

    /**
     * @return array
     */
    protected function common(): array
    {
        return Arr::get($this->config, 'targets.common', []);
    }

    /**
     * @return array<string, string>|string|null
     */
    public function paths(string $key = null, string $suffix = null): array|string|null
    {
        $paths = [
            'releases' => 'releases',
            'serve' => 'live',
            'storage' => 'storage',
            '.env' => '.env',
        ];

        if ($key) {
            return Arr::get($paths, $key).($suffix ? '/'.$suffix : null);
        }

        return $paths;
    }

    /**
     * Get the list of existing releases.
     *
     * @return array
     */
    public function releases(): array
    {
        $path = Str::before(base_path(), 'releases').$this->paths('releases', '*');

        return array_map(
            fn($release) => pathinfo($release, PATHINFO_BASENAME),
            glob($path, GLOB_ONLYDIR)
        );
    }

    /**
     * Get the active release ID.
     *
     * @return string|null
     */
    public function activeRelease(): ?string
    {
        $path = Str::before(base_path(), 'releases').'/'.$this->paths('serve');

        if (! file_exists($path)) {
            return null;
        }

        $link = readlink($path);

        return $link ? pathinfo($link, PATHINFO_FILENAME) : null;
    }

    /**
     * Check if a release actually exists.
     *
     * @param  string  $release
     * @return bool
     */
    public function releaseExists(string $release): bool
    {
        return in_array($release, $this->releases());
    }

    public function latestRelease(): string
    {
        return Arr::last($this->releases());
    }

    /**
     * Get the local Envoy command to  run.
     *
     * @param  string  $target
     * @param  string  $task
     * @param  array  $options
     * @return string
     */
    public function getEnvoyCommand(string $target, string $task, array $options = []): string
    {
        $exec = base_path('vendor/bin/envoy');

        $script = 'vendor/thepublicgood/platoon/scripts/deploy.blade.php';
        if (file_exists(base_path('Envoy.blade.php'))) {
            $script = 'Envoy.blade.php';
        }

        $ext = collect($options)->map(fn ($value, $key) => $key.'='.$value)->implode(' ');

        return $exec.' run '.$task.' --conf='.$script.' --server='.$target.' '.$ext;
    }

    public function runEnvoy(string $target, string $task, array $options = [], \Closure $cb = null): void
    {
        $command = $this->getEnvoyCommand($target, $task, $options);

        $process = Process::fromShellCommandline($command, base_path(), timeout: 0);

        $process->setTty(Process::isTtySupported());

        $process->mustRun($cb);

    }
}
