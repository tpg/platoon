# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## v2.1.0 - 2025-02-25

Laravel 12 support.

## v2.0.1 - 2024-04-17

- Recursively replace config hooks. This means you can add hooks to a server config without needing to replicate common hooks.

## v2.0.0 - 2024-03-01

- Laravel 11 support
- Dropped PHP 8.1 support

## v1.3.5 - 2024-02-26

Removed the `spatie/laravel-data` package as we don't actually use it.

## v.1.3.4 - 2023-10-18

Fixed a bug in the PHP flags config.

## v1.3.3 - 2023-10-12

- Added a `extra.php-flag` config option.
- The `-dallow_url_fopen=1` flag is now included by default every time the PHP binary is used. No need to modify your composer flags or PHP flags.
- Some documentation updates.

## v1.3.2 - 2023-10-06

- Fixes to composer dependency install.

## v1.3.0 - 2023-10-06

- A small update to fix an irritating Composer install problem.
- Dependency update.
- Pass `-dallow_url_open=1` to the PHP cli when installing composer.

## v1.2.0 - 2023-02-21

Simple one: Laravel 10 support.

## v1.1.0 - 2023-01-30

This release fixes a number of small bugs that crept into the v1.0 release, mainly around the placement of the `composer.phar` executable.

- The composer install location will now correctly default to the project root.
- The base directory config should be "root" and not "path". There were some basic config changes that didn't make it into the previous release.
- Added test support for PHP 8.2.

## v1.0.0-beta.2 - 2022-12-12

This is just a quick fix for the config validation. Hooks arrays should be nullable.

## v1.0.0-beta.1 - 2022-12-12

First beta release for version 1.

- There are some config changes (the most important being the renaming of `path` as `root`).
- A `@release` tag has been added for use in hook scripts.
- There's also been some refactoring.
- Documentation has been updated with the changes.

## v0.3.4 - 2022-12-02

- Includes a new `extra` target settings which can be used to pass additional information to the target. For now, only `composer-flag` is supported.
- Documentation has been updated.

See the **Extra options** documentation [here](https://tpg.github.io/platoon/reference/config.html#extra-options).

## v0.3.3 - 2022-11-29

- Fixed a bug in the `deploy.blade.php` script introduced with the target server name change.

## v0.3.2 - 2022-11-27

- Updated `composer.json` script to place config correctly.
- Updated `publish` command to support the Envoy script.
- Renamed the `live` server to `target` in the Envoy script.
- A few updates to the documentation

## v0.3.1 - 2022-11-23

Just a small release is update the version of `spatie/laravel-data`.

## v0.3.0 - 2022-08-11

- Added a `releases:list` command
- Added a `releases:set` command
- Added a `releases:rollback` command
- Fixed a bug in the text expander for hooks.

## v0.2.0 - 2022-08-04

This version brings basic tag expansion to the hooks system. You can now use things like `@php` or `@artisan`. The hooks have also been updated a little to ensure that remote hooks run from the project root by default.

The dedicated documentation page is now live at [https://tpg.github.io/platoon]().

## v0.1.2 - 2022-08-02

Removed the 60 second timeout for the `deploy` and `cleanup` commands.

## v0.1.1 - 2022-07-29

Fixed a bug in the Envoy script that was causing problems with the Install hook.

## v0.1.0 - 2022-07-17

There's plenty of changes with this version.

- Added a new "hooks" system. Hooks are run AFTER any of the default stuff. If you need more control than what the hooks provide, then public the Envoy script and modify that.
- There's a few tests now using PestPHP 🤸🏼‍♀️
- The `platoon:finish` command has been removed as it's no longer needed. There is a "finish" hook if you need anything complex.
- Use the "build" hook to add a build task without needing to publish the Envoy script.

## v0.0.1 - 2022-06-21

First official release!

There's plenty of stuff to come and we'll eventually replicate most of the features that Attaché provided. For now, you can use Platoon to get your deployments going. And since it's based on Laravel Envoy, it's easy to customise as needed.

- Multiple target server support
- Common deployment command
- Release clean up command
