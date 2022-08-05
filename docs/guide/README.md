---
lang: en-US
title: Guide
description: Get up and running with Platoon
---

Platoon is a simple deployment solution for Laravel. It's based around Laravel Envoy and can help create zero-downtime deployments without needing fancy hosted solutions.

## Installation
Like everything Laravel, Platoon is installed using Composer:

```shell
composer require thepublicgood/platoon
```

Once installed, you'll find a new `platoon.php` config file in your app's `config` directory and a few new Artisan commands in the `platoon` namespace. Open the config file and make changes to reflect your deployment environment. This will likely include the hostname or IP address, the SSH port, username and the path where your application will be stored.

## Configuration

You'll also need to log into your target host and ensure that you can use SSH keys to authenticate. Platoon does not support password authentication. You'll also need to ensure your web server can serve a symbolic link. If you're an Nginx user, there's likely not much you'll need to change, but for Apache, you'll need to ensure you have the `+FollowSymLinks` option set.

In your `platoon.php` config file, you need to provide a `path` setting. This is the project root directory and not the path you should point your web server at. Instead, this is the ROOT path where platoon will place everything related to your project.

Your web server will need to configured to serve the `live` symbolic link that Platoon will create during deployment. So for example, if your application root is `/opt/my/application`, then your web server should serve `/opt/my/application/live`. The `live` symbolic link will point to a directory Platoon will create in the `releases` directory.

For a more detailed explanation of the directory structure, take a look at the [Directory Structure](/reference/structure.html) section.

## Deploy
Once you're all configured and your target has been set up, you're ready to deploy. Platoon provides a simple command through Artisan. To deploy, simply run:

```shell
php ./artisan platoon:deploy
```

If you have more than one target in your config file, you can specify the target you want to deploy to:

```shell
php ./artisan platoon:deploy staging
```

By default, Platoon will select the first target in the list, otherwise you can specify the name of your default target by setting the `default` config option. Running `platoon:deploy` without specifying the target will automatically use the default target.

That's it. Your first deployment is done. You'll probably want to log back into your target and update the `.env` file and migrate databases and such. In future, new deployments will create a new directory in the `releases` directory and replace the `live` symbolic link.

## Environment
Platoon will place the `.env` file directly in the project root and create a symbolic link to it in each release. You never have to worry about your `.env` file being overwritten by a release and you have a single location when you need to update things. The same is done for the `storage` directory.

Since they are placed outside the releases, they're never overwritten. This is what makes the zero-downtime part work.

During your first deployment, Platoon will copy the storage directory from the deployment to the project root and delete the original. A new symlink will be created in each release.

## A note on migrations
Platoon can migrate database changes for you. However, it's good to know that it does so by passing the `--force` parameter to the `migrate` Artisan command. This could potentially by unsafe, as you're manipulating databases in production. The option is provided to you, but be cautious.

In addition, Your app will likely not be configured correctly to migrate databases during that first deployment. It's understood you will update your database configuration in the `.env` file after first deployment and run the migrate command yourself. Future migrations can then happen automatically.

During first deployment, Platoon will copy the `.env.example` file. It's unlikely that you will have a database configured in this file which will cause Platoon to fail if you have `migrate` set to `true`.