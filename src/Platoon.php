<?php

declare(strict_types=1);

namespace TPG\Platoon;

use TPG\Platoon\Contracts\PlatoonContract;

class Platoon implements PlatoonContract
{
    public function target(string $name): Target
    {
        return new Target($name, config('platoon.targets.'.$name));
    }
}
