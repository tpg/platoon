---
lang: en-US
title: Release Management
description: Dealing with releases
---

Platoon provides a few simple tools to help you deal with multiple releases. Each time you run a deployment, Platoon creates a new release on the target host. Releases are appropriately stored in the `releases` directory and named with the current date and time.

By default Platoon will always keep the previous release around as well as the current one. This is handy for this occations where you need to rollback to the previous version. If you need to ensure that there are more than 1 previous release on the target, you can set the `releases` config option to any number you like. The default is `2`. Setting it to  `0` will keep all releases and the `cleaup` task will never be run. This will slowly eat through disk space, so it's not recommended.

```php:
return [
    'targets' = [
        'production' => [
            //...
            'releases' => 5,
        ],
    ],
];
```

## Listing releases
You can easily see the releases that are currently on the server by running the `platoon:releases:list` command. The command takes a target name as a parameter but if you leave it out Platoon will use the default server.

## Changing the active release
