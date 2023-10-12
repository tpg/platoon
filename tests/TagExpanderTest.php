<?php

declare(strict_types=1);

it('can expand tags in an array of commands', function () {

    $commands = [
        '@php ./artisan serve',
        '@artisan horizon:terminate',
        'echo "installed to @base"',
        'echo "@release released"',
    ];

    $expander = new \TPG\Platoon\TagExpander(platoon()->target('staging', '1234567890'));

    $expanded = array_map(fn ($command) => $expander->expand($command), $commands);

    $this->assertSame([
        '/usr/bin/php ./artisan serve',
        '/usr/bin/php -dallow_url_fopen=1 /path/to/application/root/live/artisan horizon:terminate',
        'echo "installed to /path/to/application/root"',
        'echo "1234567890 released"',
    ], $expanded);
});
