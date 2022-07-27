---
lang: en-US
title: Config Reference
description: Platoon configuration file reference
---

# Configuration Reference
The Platoon configuration is simple, but since it's a normal Laravel config file (which is really just a PHP that returns an array), and Laravel developer should feel right at home. The Platoon config is in the `platoon.php` file in your config directory. It is created automatically when requiring the Platoon package.

The config file houses all deployment information for the current project.

## Targets
Targets are the hosts where your project will be deployed to. You can have as many targets as you need, but you must have at least one. A typical target configuration looks something like this:

```php
'targets' => [
    'staging' => [
        'host' => 'staging.test',
        'port' => 22,
        'username' => 'ssh-username',
        'path' => '/path/to/application/root',
        'php' => '/usr/bin/php',
        'composer' => '/path/to/composer.phar',
        'branch' => 'master',
        'migrate' => false,
    ],
]
```

The `staging` key is the name of the target. You'll use this name to reference this target when deploying. All these settings are required, but there are a few that are optional. In particular, the `assets` and `hooks` target settings are not required. You can find out more about them under the [Assets](#assets) and [Hooks](#hooks) sections.

::: tip Note
Platoon does not support password authentication. Your targets MUST be accessible using SSH keys. This means you'll need to generate a local key pair and copy the public key to the server.
:::

## Directory structure
The `path` setting refers to the ROOT directory where the project is stored. In this root directory, Platoon will place all the bits needed for your project. By default there is a `releases` directory, a `storage` directory, a `.env` file and a symbolic link named `live`. During deployment, Platoon will create symbolic links for the `.env` file and `storage` directory into a new release directory created in `releases`. A typical project directory structure looks something like this:

```
/path/to/project/
    |
    +- .env
    |
    +- live/  --> /path/to/project/releases/202201010000
    |
    +- releases/
    |   |
    |   +- 202201010000/
    |       |
    |       +- .env  --> /path/to/project/.env
    |       |
    |       +- storate/  --> /path/to/project/storage/
    |
    +- storage/
```

If you need to make changes to the `.env` file or the contents of the `storage` directory, you do so in the project root. This is the magic behind the zero-downtime deployment.

## Default target
Since it may be common to have more than one target, it's handy to have one of them set as the default. You can do this by supplying the name of one of the targets to the `default` config setting. Platoon will then use this target without you needing to specify it when deploying. If you don't specify a default in the config, then Platoon will assume that the first target specified is the default.

You can disable the default target completely by setting the `default` option to `false`. This can be useful if you are worried about deploying to the wrong host. Without a default, you're forced to make a choice about which target to deploy to.

## Common target settings
There are situations where your targets might actually be the same phyiscal server. In this case, you can set up a `common` target and provide details that can be used across all the targets you specify. Anything you set for any particular target will then overwrite the common settings. To create a common target, simply name it `common`. You can provide any target setting to the common target and it will be the default set for all your other targets.

For example, you might want to set a common hostname, username and port number:

```php
return [
    'targets' => [
        'common' => [
            'host' => 'common.host',
            'port' => 22,
            'username' => 'user',
            'php' => '/usr/bin/php',
            'composer' => '/path/to/composer.phar',
        ],
        'staging' => [
            'path' => '/opt/app/staging',
            'migrate' => true,
            'branch' => 'develop',
        ],
        'production' => [
            'path' => '/opt/app/production',
            'migrate' => false,
            'branch' => 'master',
        ]
    ]
]
```

## Assets
It's strongly recommended that you never place compiled assets in your projects Git repository. It just makes things messy. Instead, assets should be compiled either on the target, or compiled and copied to the target. The latter is a common approach when dealing with a CI/CD pipeline. You can specify a list of assets to copy using the `assets` array on your target. You can also specify `assets` on the common target and it will be used for all other targets automatically.

The `assets` array is a key value pair. The key being the local file, and the value being the remote file.

```php
'staging' => [
    //...
    'assets' => [
        'public/js/app.js' => 'public/js/app.js',
        'public/css/app.css' => 'public/css/app.css',
        'public/images/*' => 'public/images',
    ]
],
```

::: tip Take Note
When specifying whole directories, take note that if you specify a directory that already exists, the assets will be copied to a new directory INSIDE the directory you specified. This is likely NOT what you want. The example above gives a solution to getting around this issue.

Directories are always copied recursively, so you don't need to specify any children paths.
:::

## Cleaning up
One of the final tasks that Platoon will complete is to clean up any old releases on the target. By default the current release and the previous one are left untouched and all other releases are removed. This will allow you to backtrack to a previous release if changes you make cause problems after deployment.

## Hooks
Hooks are a simple way to add functionality to each task without needing to modify the Envoy script. This is handly if, for example. you need to provide your own build steps before deploying. Each step has a post hook, which means that scripts are executed AFTER the default scripts. There are currently 10 tasks. You can add hooks per target or to the common target config. Hooks must exist inside the `hooks` array and must be an array of bash commands:

```php
'targets': [
    'staging': [
        //...
        'hooks' => [
            'build' => [
                'yarn production',
            ],
            'finish' => [
                'php ./artisan horizon:terminate'
            ],
        ]
    ]
]
```

The the exception of `build` and `assets`, all tasks are run on the remote server. The following hooks are available:

| Hook | Where | Description |
|------|-------|-------------|
| `build` | local | Empty by default. Use it to run any tasks BEFORE starting the deployment |
| `install` | remote | Clone the repository into a new release directory |
| `prep` | remote | Create symbolic links to `.env` and `storage` in the project root |
| `composer` | remote | Install or update Composer |
| `dependencies` | remote | Install Composer dependencies |
| `assets` | local | Copy any specified assets to the remote |
| `database` | remote | Migrate any database changes |
| `live` | remote | Create the `live` symbolic link effective making the project live. |
| `cleanup` | remote | Remove any old releases |
| `finish` | remote | Run any final deployment tasks |