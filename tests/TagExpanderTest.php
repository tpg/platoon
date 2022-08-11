<?php

declare(strict_types=1);

it('can expand tags in an array of commands', function () {

    $commands = [
        '@php ./artisan serve',
        '@artisan horizon:terminate',
        'echo "installed to @base"',
    ];

    $expander = new \TPG\Platoon\TagExpander(platoon()->target('staging'));

    $expanded = array_map(fn ($command) => $expander->expand($command), $commands);

    $this->assertSame([
        '/usr/bin/php ./artisan serve',
        '/usr/bin/php /path/to/application/root/live/artisan horizon:terminate',
        'echo "installed to /path/to/application/root"',
    ], $expanded);
});
