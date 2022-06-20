# Simplified Laravel Envoy deployments

Platoon is a simple Laravel package designed to make deployments dead simple. Platoon is really just a wrapper around Laravel Envoy and provides its own customised Envoy script.

## Status
Platoon is still fairly new and you shouldn't use this with mission critical stuff unless you're super happy with taking a bit of risk. However, with that said, I use this script all the time and its based on a process I've been using for years. It was the heart of the Attache package I wrote some years back, and I now use Platoon in my own CI/CD pipeline.

## What about Attache?
So Attache was great. I used it a lot for a while and it still gets used a bunch on some older projects, but it's been neglected as I don't use it myself much anymore. Mainly because it doesn't fit all that well into my CI/CD pipeline. I've been slowly migrating back to Envoy, but I missed some of the simplicity of Attache, so this is my attempt to make my daily use of Envoy just a little more delightful.

Attache also had some seriously complex parts that I just didn't have the time to maintain anymore.

## What about tests?
Yeah, there's no tests here yet. Which is why it's also not version 1 yet. I wrote this as a quick, personal thing so I never actually wrote any tests. But I'll get to it eventually.

---

## Installation
Add the package to your Laravel app:

```shell
composer require thepublicgood/platoon
```

Once the package is installed, publish the config file:

```shell
php ./artisan platoon:publish
```

## Getting Started
This will add a `platoon.php` file to the `config` directory. In the config file, specify your deployment targets. You need to have at least one target defined:

```php
return [
    'targets' => [
        'staging' => [
            'host' => 'target.host',  
            'port' => 22,  
            'username' => 'username',  
            'path' => '/path/to/project',  
            'php' => '/usr/bin/php',  
            'composer' => '/path/to/composer.phar',  
            'branch' => 'master',  
            'migrate' => false,  
        ],
        'production' => [
            //...
        ]
    ],
]
```

You can configure as many targets as you need. If you deploy to one specific target more often than others, you can set the `default` option to the name of that target:

```php
return [
    'default' => 'staging',
]
```

To deploy to a target, use the `platoon:deploy` Artisan command:

```shell
php ./artisan platoon:deploy production
```

If you don't specify a server, or there is no default set in your config file, Platoon will deploy to the first server in your targets array.

## Structure
Platoon will create the following directory structure in the target directory path:

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

When you first deploy using Platoon, the `storage` directory will be moved to the project root and the current `.env.example` file will be placed in the project root directory as `.env`. The repository will be cloned into a new directory inside the `releases` directory and named with the current date and time. The `storage` directory and `.env` file are then symlinked into the new "release".

Lastly, the new release is then symlinked as `live` in the project root directory. You'll need to configure your web server to serve the `live` symlink.

## Composer
If you already have composer installed somewhere on the target server, you can specify its location in the config file. However, if not, Platoon will install it for you at the location you specify. Composer will also always be run using the PHP binary you specify in the config file. For example, if you config file looks like this:

```php
"staging" => [
    "php" => '/usr/local/bin/php8.1',
    "composer" => '/usr/local/bin/composer',
]
```

Whenever Platoon needs to run composer, it will construct the command like this:

```shell
/usr/local/bin/php8.1 /user/local/bin/composer ...
```

## Cleaning up
Platoon includes a `platoon:cleanup` command. You should never need to run this command and in-fact, is hidden when your applications environment is set to `local`. Once a deployment is completed, the `platoon:cleanup` command will remove any old releases on the server automatically. The current release and the previous release are left intact, so if you ever need to rollback, doing so would require linking the previous release as the `live` symbolic link.

## The Envoy script
Platoon comes with its own Envoy script. In most cases, you shouldn't need to alter the script, but if you feel like you need to, you can publish it to your project with:

```shell
php ./artisan vendor:publish --tag=platoon-script
```

This will place an `Envoy.blade.php` file in your project folder. You can still use the `platoon:deploy` command and it will simply use your `Envoy.blade.php` file instead of it's own one.

The Envoy script has 8 tasks that it runs through:

### 1. Build
The build task is used to bundle JavaScript, or any other build steps you need to take. Platoon does not include any build steps by default, and you might want to run your build steps through your CI directly. If you do need to use this task, then you'll need to publish the Envoy script and edit the `build` task (hint: it's the first task in the file).

### 2. Install
The `install` task will clone the repository into a new release directory. It will also check if there is a `release` directory in the first place and create it if needed.

### 3. prep
The `prep` task will check to see if the `storage` directory has been moved. If not, then it assumes that this is the first time you're deploying to this target and it will move the `storage` directory and the `.env` file for you.

It will then create symbolic links in the new release to the `storage` and `.env` locations in the project root.

### 4. dependencies
The `dependencies` step will install composer dependencies. The following flags are passed to the `composer install` command:

```shell
--prefer-dist --no-dev --no-progress --optimize-autoloader
```

### 5. database
The `database` task will migrate database changes, but only if you have set `migrate` to `true` in your config file. This can be a destructive task if you're not careful, so it might be better to run your migrations manually so you can check them first. If you're confident, then set it to `true` and the `migrate` command will be run with the `--force` flag.

### 6. live
This is the last task to take the new release live. This step simply creates the `live` symbolic link to the new release. It will also run `artisan storage:link` to create a new link to the `storage/app/public` in the `public` directory.

### 7. cleanup
The `cleanup` step will run the `platoon:cleanup` command on the server. This will remove any old releases that may still be hanging around. By default the command will ensure at least 2 releases are available, but you can change this by passing a number to the `--keep` flag.

### 8. finish
The last task will simply output the name of the release that is now live.
