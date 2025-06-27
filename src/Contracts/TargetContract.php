<?php

declare(strict_types=1);

namespace TPG\Platoon\Contracts;

interface TargetContract
{
    public function __construct(string $name, array $config);
    public function config(string $key, mixed $default = null): mixed;
    public function paths(string $pathName, ?string $suffix = null): string;
    public function composer(): string;
    public function php(): string;
    public function composerFlags(): string;
    public function phpFlags(): string;
    public function artisan(?string $release = null): string;
    public function assets(string $release): array;
    public function hooks(string $step): array;
}
