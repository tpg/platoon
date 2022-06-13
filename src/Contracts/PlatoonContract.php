<?php

declare(strict_types=1);

namespace TPG\Platoon\Contracts;

use TPG\Platoon\Target;

interface PlatoonContract
{
    public function target(string $name): Target;
}
