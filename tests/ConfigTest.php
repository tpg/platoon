<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use TPG\Platoon\Console\DeployCommand;

it('will validate the config file', function () {

    config(['platoon' => []]);

    $platoon = new TPG\Platoon\Platoon();

    $errors = $platoon->validateConfig();

    $this->assertArrayHasKey('repo', $errors);
    $this->assertArrayHasKey('targets', $errors);
});

it('will throw an except on invalid config', function () {

    config(['platoon' => []]);

    $this->expectException(ValidationException::class);
    Artisan::call(DeployCommand::class);

});
