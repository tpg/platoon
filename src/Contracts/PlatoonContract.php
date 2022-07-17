<?php

declare(strict_types=1);

namespace TPG\Platoon\Contracts;

use Illuminate\Support\Collection;
use TPG\Platoon\Target;

interface PlatoonContract
{
    /**
     * @return Collection<Target>
     */
    public function targets(): Collection;

    /**
     * @param  string  $name
     * @return Target
     */
    public function target(string $name): Target;

    /**
     * @return Target
     */
    public function defaultTarget(): Target;
}
