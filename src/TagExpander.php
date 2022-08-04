<?php

declare(strict_types=1);

namespace TPG\Platoon;

class TagExpander
{
    public function __construct(protected Target $target)
    {
    }

    public function expand(string|array $commands): array
    {
        if (is_string($commands)) {
            $commands = [$commands];
        }

        return array_map(fn ($command) => $this->expandString($command), $commands);
    }

    protected function expandString(string $command): string
    {
        $replacement = [
            '/@php/' => $this->target->php,
            '/@artisan/' => $this->target->php.' '.$this->target->paths('serve').'/artisan',
            '/@composer/' => $this->target->php.' '.$this->target->composer,
            '/@base/' => $this->target->path,
        ];

        return preg_replace(array_keys($replacement), array_values($replacement), $command);
    }
}
