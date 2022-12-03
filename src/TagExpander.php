<?php

declare(strict_types=1);

namespace TPG\Platoon;

class TagExpander
{
    public function __construct(protected Target $target)
    {
    }

    public function expand(string $command): ?string
    {
        $replacement = [
            '/@php/' => $this->target->php,
            '/@artisan/' => $this->target->artisan(),
            '/@composer/' => $this->target->composer(),
            '/@base/' => $this->target->root,
            '/@release/' => $this->target->release,
        ];

        return preg_replace(array_keys($replacement), array_values($replacement), $command);
    }
}
