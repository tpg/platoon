<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
     * @return Target
     */
    public function target(string $name): Target
    {
        $data = array_merge($this->common(), Arr::get($this->config, 'targets.'.$name));

        return new Target($name, $data);
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
}
