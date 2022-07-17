<?php

declare(strict_types=1);

beforeEach(function () {

    $this->helper = new \TPG\Platoon\Helpers\Envoy();

});

it('it will return a target', function  () {
    $target = $this->helper->target();

    $this->assertSame('staging.test', $target->host);
});

it('will create a new release timestamp', function () {

    $timestamp = date('YmdHis');
    $release = $this->helper->newRelease('pre-', '-suf');

    $this->assertSame('pre-'.$timestamp.'-suf', $release);
});
