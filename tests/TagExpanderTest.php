<?php

declare(strict_types=1);

it('can expand tags in an array of commands', function () {

    $commands = [
        '@php ./artisan serve',
        '@artisan horizon:terminate',
        'echo "@release released"',
        'echo "installed to @base',
    ];

    $expander = new \TPG\Platoon\TagExpander(platoon()->target('staging'));

});
