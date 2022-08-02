<?php

declare(strict_types=1);

namespace TPG\Platoon;

class TagExpander
{
    public function __construct(protected Target $target)
    {
    }

    public function expand(string|array $commands): string
    {
        if (is_string($commands)) {
            $commands = [$commands];
        }

        array_map(fn ($command) => $this->expandString($command), $commands);
    }

    protected function expandString(string $command): string
    {
        $tags = preg_match_all('/\@(?<tag>[a-z]+)\b/', $command);
        dd($tags);
    }
}
