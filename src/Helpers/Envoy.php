<?php

declare(strict_types=1);

namespace TPG\Platoon\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use TPG\Platoon\Platoon;
use TPG\Platoon\Target;

class Envoy
{
    public readonly string $localhost;
    protected readonly array $config;
    protected readonly Collection $targets;
    protected readonly Platoon $platoon;

    public function __construct()
    {
        $this->localhost = '127.0.0.1';

        $this->config = include(getcwd().'/config/platoon.php');

        $this->platoon = new Platoon($this->config);

        if ($this->platoon->targets()->count() === 0) {
            throw new \RuntimeException('No targets specified');
        }
    }

    public function config(string $key): mixed
    {
        return Arr::get($this->config, $key);
    }

    public function target(?string $name = null): Target
    {
        if (! $name) {
            return $this->platoon->defaultTarget();
        }

        if (! $this->platoon->targets()->has($name)) {
            throw new \RuntimeException('No target with name "'.$name.'"');
        }

        return $this->platoon->target($name);
    }

    public function repo(): string
    {
        return Arr::get($this->config, 'repo');
    }

    public function newRelease(string $prefix = null, string $suffix = null): string
    {
        date_default_timezone_set('UTC');
        return $prefix.date('YmdHis').$suffix;
    }

}
