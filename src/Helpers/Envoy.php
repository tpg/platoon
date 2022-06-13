<?php

declare(strict_types=1);

namespace TPG\Platoon\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use TPG\Platoon\Target;

class Envoy
{
    public readonly string $localhost;
    protected readonly array $config;
    protected readonly Collection $targets;

    public function __construct()
    {
        $this->localhost = '127.0.0.1';

        $this->config = include(getcwd().'/config/platoon.php');


        $this->loadTargets();
    }

    protected function loadTargets(): void
    {
        $this->targets = collect(Arr::get($this->config, 'targets'))->map(fn ($config, $key) => new Target($key, $config));

        if ($this->targets->count() === 0) {
            throw new \RuntimeException('No targets specified');
        }

        $this->targets->keyBy(fn (Target $target) => $target->name);
    }

    public function target(?string $name = null): Target
    {
        if (! $name) {
            return $this->getDefaultTarget();
        }

        if (! $this->targets->has($name)) {
            throw new \RuntimeException('No target with name "'.$name.'"');
        }

        return $this->targets->get($name);
    }

    protected function getDefaultTarget(): Target
    {
        $defaultName = Arr::get($this->config, 'default');

        if ($defaultName && ! $this->targets->has($defaultName)) {
            throw new \RuntimeException('No target with name "'.$defaultName.'"');
        }

        if (! $defaultName) {
            return $this->targets->first();
        }

        return $this->targets->get($defaultName);
    }

    public function repo(): string
    {
        return Arr::get($this->config, 'repo');
    }

    public function newRelease(string $prefix = null, string $suffix = null): string
    {
        date_default_timezone_set('Africa/Johannesburg');
        return $prefix.date('YmdHis').$suffix;
    }

}
