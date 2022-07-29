# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
