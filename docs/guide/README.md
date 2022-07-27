---
lang: en-US
title: Guide
description: Get up and running with Platoon
---

Platoon is a simple deployment solution for Laravel based on Envoy. It helps create zero-downtime deployments without needing fancy hosted solutions. Platoon is a Laravel package that's built around Envoy, the simple task runner from Laravel.

## Installation
Like everything Laravel, Platoon is installed using Composer:

```shell
composer require thepublicgood/platoon
```

Once installed, you'll find a new `platoon.php` config file in your `config` directory and a few new Artisan commands in the `platoon` namespace. Open the config file and make changes to reflect your deployment environment. This will likely include the hostname or IP address, the SSH port, username and the path where your application will be stored.

## Configuration

You'll also need to log into your target host and ensure that you can use SSH keys to authenticate. Platoon does not support password authentication. You'll also need to ensure your web server can serve a symbolic link.

In your `platoon.php` config file, you need to provide a `path` setting. This is the project root directory and not the path you should point your web server at. Instead, this is the ROOT path where platoon will place everything related to your project. Instead, you'll need to point your web server to the `live` symbolic link that Platoon will create. So for example, if your application root is `/opt/my/application`, then your web server should serve `/opt/my/application/live`. The `live` symbolic link will point to a release directory created in the `releases` directory.

For a more detailed explanation of the directory structure, take a look at the [Directory Structure](/reference/structure.html) section.

## Deploy
Once you're all configured and your target has been set up, you're ready to deploy. Platoon provides a simple deployment tool through Artisan, so to deploy, simply run:

```shell
php ./artisan platoon:deploy
```

If you have more than one target in your config file, you can specify the target you want to deploy to:

```shell
php ./artisan platoon:deploy staging
```

By default, Platoon will select the first target in the list, otherwise you can specify the name of your default target by setting the `default` config option. Running `platoon:deploy` without specifying the target will automatically use the default target.

That's it. Your first deployment is done. You'll probably want to log back into your target and update the `.env` file and migrate databases and such. In future, new deployments will create a new release in the `releases` directory and replace the `live` symbolic link.

## Environment
Platoon will place the `.env` file directly in the project root and create a symbolic link to it in each release. You never have to worry about your `.env` file being overwritten by a release and you have a single location when you need to update things. The same is try for the `storage` directory.

During your first deployment, Platoon will copy the storage directory from the deployment to the project root and delete the original. A new symlink will be created in each release.
