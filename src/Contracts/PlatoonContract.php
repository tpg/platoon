<?php

declare(strict_types=1);

namespace TPG\Platoon\Contracts;

use Closure;
use Illuminate\Support\Collection;
use TPG\Platoon\Target;

interface PlatoonContract
{
    /**
     * @return Collection<Target>
     */
    public function targets(): Collection;

    /**
     * Validates the Platoon configuration and returns an array of error messages.
     *
     * @return array
     */
    public function validateConfig(): array;

    /**
     * @param  string  $name
     * @param  string|null  $release
     * @return Target
     */
    public function target(string $name, ?string $release = null): Target;

    /**
     * @return Target
     */
    public function defaultTarget(): Target;

    /**
     * @return array<string, string>|string|null
     */
    public function paths(string $key = null, string $suffix = null): array|string|null;

    /**
     * Get the list of existing releases.
     *
     * @return array
     */
    public function releases(): array;

    /**
     * Get the active release ID.
     *
     * @return string|null
     */
    public function activeRelease(): ?string;

    /**
     * Check if a release actually exists.
     *
     * @param  string  $release
     * @return bool
     */
    public function releaseExists(string $release): bool;

    /**
     * Get the latest release ID.
     *
     * @return string
     */
    public function latestRelease(): string;

    /**
     * Get the local Envoy command to  run.
     *
     * @param  string  $target
     * @param  string  $task
     * @param  array  $options
     * @return string
     */
    public function getEnvoyCommand(string $target, string $task, array $options = []): string;

    /**
     * Run the  specified Envoy task.
     *
     * @param  string  $target
     * @param  string  $task
     * @param  array  $options
     * @param  Closure|null  $cb
     * @return void
     */
    public function runEnvoy(string $target, string $task, array $options = [], Closure $cb = null): void;
}
