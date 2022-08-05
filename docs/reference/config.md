---
lang: en-US
title: Config Reference
description: Platoon configuration file reference
---

# Configuration Reference
The Platoon configuration is simple, and since it's a normal Laravel config file (which is really just a PHP file that returns an array), Laravel developers should feel right at home. The Platoon config is in the `platoon.php` file in your config directory. It is created automatically when requiring the Platoon package.

The config file houses all deployment information for the current project and you'll want to include this file in your repository. Platoon does not support password authentication, so you should not be including any sensitive authentication information in the config file.

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

The `staging` key is the name of the target. You'll use this name to reference this target when deploying. The only required options are `host`, `username` and `path`. All the others have default values. There are also `assets` and `hooks` settings that are not required. You can find out more about them under the [Assets](#assets) and [Hooks](#hooks) sections.

::: tip Note
Platoon does not support password authentication. Your targets MUST be accessible using SSH keys. This means you'll need to generate a local key pair and copy the public key to the server.
:::

| Target setting | Default | Description |
| `host` | - | The hostname or IP address of the remote target |
| `port` | 22 | The SSH port number |
| `username` | - | The SSH username |
| `path` | - | The path to the project root |
| `php` | `/usr/bin/php` | The full path to the PHP binary |
| `composer` | `<project-root>/composer.phar` | The full path to where composer.phar is stored |
| `branch` | `main` | The branch to clone |
| `migrate` | `false` | Migrate database changes on the target |
| `assets` | `[]` | Assets to copy during deployment. See [Assets](#assets) |
| `hooks` | `[]` | The hooks to run. See [Hooks](#hooks) |
| `paths` | `[]` | Any changes to directory structure. See [Directory Structure](#directory-structure) |
## Directory structure
The `path` setting refers to the ROOT directory where the project is stored. In this root directory, Platoon will place all the bits needed for your project. By default there is a `releases` directory, a `storage` directory, a `.env` file and a symbolic link named `live`. During deployment, Platoon will create symbolic links for the `.env` file and `storage` directory into a new directory created in `releases`. A typical project directory structure looks something like this:

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

You can change the names of the directories that Platoon will create. In most cases, you should leave these settings alone, but if the need arises, you can pass an array to the `paths` config in your `platoon.php` target config:

```php
'targets' => [
    'staging' => [
        //...
        'paths' => [
            'releases' => 'deployments',
            'live' => 'serve',
            'storage' => 'stuff',
            '.env' => 'config',
        ]
    ]
]
```

| Path Config | Default | Description |
| `releases`  | `releases` | The directory where new releases are deployed |
| `live` | `live` | The name of the symbolic link that the web server should serve |
| `storage` | `storage` | The name of the Laravel storage directory |
| `.env` | `.env` | The name of the laravel `.env` file |

Note that the `.env` and `storage` paths will always be named correctly when symlinked into the release. These settings allow you to change the name of the paths that Platoon will create.

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
            'branch' => 'main',
        ]
    ]
]
```

## Assets
It's strongly recommended that you never place compiled assets in your projects Git repository. It just makes things messy. Instead, assets should be compiled either on the target, or compiled and copied to the target during deployment. The latter is a common approach when dealing with a CI/CD pipeline. You can specify a list of assets to copy using the `assets` array on your target. You can also specify `assets` on the common target and it will be used for all other targets automatically.

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
When specifying whole directories, take note that if you specify a directory that already exists, the assets will be copied to a new directory INSIDE the directory you specified. This is likely NOT what you want and can be annoying to debug.

Directories are always copied recursively, so you don't need to specify any child paths.
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

With the exception of `build` and `assets`, all tasks are run on the remote server. The following hooks are available:

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

All hooks run on the remote target are executed within the project root path.

Platoon hooks also include a set of expansion tags that can make writing hooks a whole lot easier. For example, you can reference the configured PHP executable by using the `@php` tag. This means, you could write the previous `finish` hook like this:

```php
'finish' => [
    '@php ./artisan horizon:terminate'
],
```

However, there is also a `@artisan` tag which will basically do that same thing inside the release directory:

```php
'finish' => [
    '@artisan horizon:terminate'
],
```

This way you never have to worry about having the correct paths.

The following tags are provided:

| Tag | Expansion |
|-----|-----------|
| `@php` | The full path to the configured PHP binary |
| `@artisan` | The full path to the artisan script prefixed with the configured PHP binary |
| `@composer` | The path to the configured composer phar |
| `@base` | The project base directory |
