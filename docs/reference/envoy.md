---
lang: en-US
title: Envoy Reference
description: The Platoon Envoy script
---

Platoon is really just a wrapper around Envoy. You could do everything that Platoon does using Envoy alone. However, Platoon is designed to make your life just a little bit easier. Envoy is fantastic and is really not all that complicated. Getting zero-downtime deployments right can be a little tricky as there's a bunch of steps to get through, so this is where Platoon is really useful.

## The script
Since Platoon just wraps Laravel Envoy, there is a default Envoy script at it's heart. If you feel the need to modify the script, you can publish the Envoy script with:

```shell
php ./artisan vendor:publish --tag=platoon-envoy
```

This will place an `Envoy.blade.php` file at the root of app. If you're interested in learning more about Envoy (and we strongly encourage you to do so), take a look through the [documentation](https://laravel.com/docs/envoy).

You shouldn't actually need to publish the Envoy script, and if you're simply trying to add commands to each task, take a look at [Hooks](/reference/config.html#hooks) in the config reference. You can add functionality to each step without needing the Envoy script at all.

The Envoy script is broken up into the separate Platoon tasks (build, install, composer, etc...), and are run in sequence using the `deploy` story:

```php
@story('deploy')
    build
    install
    prep
    composer
    dependencies
    assets
    database
    live
    cleanup
    finish
@endstory
```

You can add or remove tasks from the story as you need to. For example, let's say you wanted to add a task that reloaded the Supervisor configuration, and you wanted to run this BEFORE the cleanup task. Add a new task block just above this story block like this:

```php
@task('supervisor', ['on' => 'live'])

supervisorctl reload

@endtask
```

Now update the story block:

```php{10}
@story('deploy')
    build
    install
    prep
    composer
    dependencies
    assets
    database
    live
    supervisor
    cleanup
    finish
@endstory
```

When creating new tasks, you can choose to run the task either on the local environment (where Platoon is being run) or on the remote server. Our new `supervisor` task will run on the server by specifying `['on' => 'live']`. To run the task locally, you could change this to `['on' => 'local']`.

## Envoy helper
Platoon provides a simple helper class for use in the Envoy script. If the script provided by Platoon isn't to your liking and you want to build your own script, you can still use the Platoon Envoy helper in your own scripts. You can reference the Platoon provided script for ideas on how the helper can be used.

At the very top of the script you should find the `@setup` block which looks something like this:

```php
@include ('./vendor/autoload.php')

@setup

$helper = new TPG\Platoon\Helpers\Envoy;

$release = $helper->newRelease();

$target = $helper->target($server);

@endsetup
```

To make this work, take note of how the the Composer `autoload` script is included.

::: tip Note
For obvious reasons, Envoy does not boot an entire Laravel application. You will not have access to your actual app or any configuration. To get round this, the Envoy helper imports it's own configuration only. Anything you place in the `platoon.php` config file will be accessible through the helper, but that's it. You won't have access to stuff inside the `app.php` config.
:::

You can get hold of any Platoon configuration option through the helper with the `config()` method. In most cases, you should not need to be grabbing configuration items, but the method is there if you need it:

```php
$helper->config('targets.common.host');
```

The first task of the helper is to create a new release by calling `$helper->newRelease()`. This will return the name of the directory that will be created inside the `releases` directory. Since this name is needed a bunch, it's a good idea to keep a copy of it. Next, the helper also needs to provide the deployment target. The `target($server)` method returns an instance of `Target` which in turn provides all the details of the specified target.

### Paths
The `paths()` method can be used to fetch a fully qualified project path. There are four defined paths: `releases`, `live`, `storage` and `.env`. You can also get the project root by using the `path` property on the `Target` instance.

```php
$target->path;  // Project root: /path/to/application
$target->paths('releases');     // /path/to/application/releases
$target->paths('live');         // /path/to/application/live
$target->paths('storage');      // /path/to/application/storage
$target->paths('.env');         // /path/to/application/.env
```

The `paths` method also accepts a second parameter as a suffix. This can be useful if you need to get to the deployed release path:

```php
$target->paths('releases', $release);   // /path/to/application/releases/1234567890
```

### Executables
Platoon needs to know where the PHP, and Composer binaries are. This is done because it's not uncommon to find multiple versions of PHP on the same host. These values can be configured per target with the `php` and `composer` settings. The `Target` instance provides these values through the `php` and `composer` properties respectively:

```php
$target->php;       // Path specified as the `php` config
$target->composer;  // Path specified as the `composer` config
```

However, since Composer would need to be run using the same PHP executable, there is a `composer()` method that does this for you:

```php
$target->composer();    // /path/to/php /path/to/composer
```

Likewize, there is an `artisan()` method that does something similar:

```php
$target->artisan();     // /path/to/php /path/to/application/live/artisan
```

::: tip Note
Note the difference between `$target->composer` and `$target->composer()`.
:::

### Assets
Since assets are always copied into the release directory, the `assets()` method takes the name of the release returned by the `newRelease()` method on the helper. The `assets` method will then alter the assets array to include the full target path.

```php
$target->assets('54321');

/*
['public/localfile.test' => 'public/remotefile.test']

 becomes...

['public/localfile.test' => '/path/to/application/releases/54321/public/remotefile.test']
/*
```

### Hooks
The `hooks` method will return an array of scripts specified in the target config. The method requires the name of the hook as the only parameter.

```php
$target->hooks('build');
```

The `hooks` method will always return an array of commands, so you'll need to loop through them in an Envoy script. The script supplied with Platoon does exactly this:

```php
@foreach ($target->hooks('build') as $step)
    {{ $step }}
@endforeach
```

To add hooks to the previous `supervisor` task, the result could look as follows:

```php
@task('supervisor', ['on' => 'live'])

supervisorctl reload

@foreach ($target->hooks('supervisor') as $step)
    {{ $step }}
@endforeach

@endtask
```
