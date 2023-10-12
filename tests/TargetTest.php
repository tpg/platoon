<?php

declare(strict_types=1);

it('will return a list of targets', function () {

    $this->assertInstanceOf(\Illuminate\Support\Collection::class, platoon()->targets());
    $this->assertCount(1, platoon()->targets());

});

it('will return a specific target', function () {
    $this->assertInstanceOf(\TPG\Platoon\Target::class, platoon()->target('staging'));
    $this->assertEquals('staging.test', platoon()->target('staging')->host);
});

it('will return a default target', function () {
    $this->assertEquals('staging.test', platoon()->defaultTarget()->host);
});

it('will throw and exception if default target is invalid', function () {

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('No target with name "staging"');
    config(['platoon.targets' => []]);

    platoon()->defaultTarget();
});

it('will return the first target if no default is defined', function () {

    config(['platoon.default' => null]);

    $default = platoon()->defaultTarget();

    $this->assertEquals('staging.test', $default->host);

});

it('will return the requested path', function () {

    $target = platoon()->defaultTarget();

    $this->assertSame($target->root.'/releases', $target->paths('releases'));

});

it('will return the fully qualified Composer path', function () {

    $target = platoon()->defaultTarget();

    $this->assertSame($target->php.' -dallow_url_fopen=1 '.$target->composer, $target->composer());

});

it('will return a string of default composer flags', function () {

    $target = platoon()->defaultTarget();

    $this->assertSame('--no-progress --no-dev --optimize-autoloader', $target->composerFlags());

});

it('will return a string of default php flags', function () {
    $target = platoon()->defaultTarget();

    $this->assertSame('-dallow_url_fopen=1', $target->phpFlags());
});

it('will return a string of custom composer flags', function () {

    config(['platoon.targets.staging.extra.composer-flags' => [
        '--dev',
        '--prefer-install=source'
    ]]);

    $target = platoon()->defaultTarget();

    $this->assertSame('--no-progress --dev --prefer-install=source', $target->composerFlags());

});

it('will return the fully qualified Artisan path', function () {
    $target = platoon()->defaultTarget();

    $this->assertSame($target->php.' -dallow_url_fopen=1 '.$target->paths('serve').'/artisan', $target->artisan(true));

});

it('will throw an exception if the path name doesnt exist', function () {
    $target = platoon()->defaultTarget();

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('No defined path named badpath');
    $target->paths('badpath');
});

it('will return an array of assets ready for scp', function () {

    config(['platoon.targets.staging.assets' => [
        'localasset.test' => 'remoteasset.test'
    ]]);

    $target = platoon()->defaultTarget();
    $assets = $target->assets('12345');

    $this->assertSame([
        'localasset.test' => $target->username.'@'.$target->host.':'.$target->paths('releases', '12345').'/remoteasset.test',
    ], $assets);
});
