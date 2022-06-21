@include ('./vendor/autoload.php')

{{-- Platoon Deployment Script
-------------------------------------------------------------------
This Envoy script is part of the Platoon deployment toolset.
You can edit this file if you want, but in most cases this
should suite most projects.
-------------------------------------------------------------------}}
@setup

$helper = new TPG\Platoon\Helpers\Envoy;

$release = $helper->newRelease();

$target = $helper->target($server);

@endsetup

@servers(['local' => $helper->localhost, 'live' => $target->hostString])

{{-- Build task
-------------------------------------------------------------------
If you need to build anything (like bundle JavaScript for
production) then this is the place to do it.
-------------------------------------------------------------------}}
@task('build', ['on' => 'local'])

# place your build tasks here

@endtask

{{-- Installation task
-------------------------------------------------------------------
This task gets the software onto the server. It will clone the
repo and link the .env file and storage directory.
-------------------------------------------------------------------}}
@task('install', ['on' => 'live'])

echo "Installing."
[[ ! -d "{{ $target->paths('releases') }}" ]] && mkdir {{ $target->paths('releases') }}

cd {{ $target->paths('releases') }}
git clone --depth 50 -b {{ $target->branch }} "{{ $helper->repo() }}" {{ $release }}

@endtask


{{-- Prep task
-------------------------------------------------------------------
Prepare the target directory for the project.
-------------------------------------------------------------------}}
@task('prep', ['on' => 'live'])

echo "Preparing installation."
cd {{ $target->path }}

if [[ ! -d "{{ $target->paths('storage') }}" ]]
then
    cp -r {{ $target->paths('releases', $release) }}/storage {{ $target->path }}/storage
    cp {{ $target->paths('releases', $release) }}/.env.example {{ $target->path }}/.env
fi

rm -rf {{ $target->paths('releases', $release) }}/storage
rm -f {{ $target->paths('releases', $release) }}/.env

ln -nfs {{ $target->path }}/.env {{ $target->paths('releases', $release) }}/.env
ln -nfs {{ $target->path }}/storage {{ $target->paths('releases', $release) }}/storage

@endtask

{{-- Composer installation
-------------------------------------------------------------------
Check if composer exists at the specified path. If not, then
download the latest release.
-------------------------------------------------------------------}}
@task('composer', ['on' => 'live'])

# Check if composer exists and install it.

if [[ ! -f "{{ $target->composer }}" ]]
then
    echo "Installing composer."
    cd {{ $target->path }}
    EXPECTED_CHECKSUM="$({{ $target->php }} -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    {{ $target->php }} -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$({{ $target->php }} -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
    then
        >&2 echo 'ERROR: Invalid installer checksum'
        rm composer-setup.php
        exit 1
    fi

    {{ $target->php }} composer-setup.php

    if [ "{{ $target->composer }}" != "{{ $target->path }}/composer.phar" ]
    then
        mv composer.phar {{ $target->composer }}
    fi
    rm composer-setup.php
fi

@endtask

{{-- Composer dependencies task
-------------------------------------------------------------------
This task gets the software onto the server. It will clone the
repo and link the .env file and storage directory.
-------------------------------------------------------------------}}
@task('dependencies', ['on' => 'live'])

echo "Installing composer dependencies."

cd {{ $target->paths('releases', $release) }}
{{ $target->composer() }} self-update
{{ $target->composer() }} install --prefer-dist --no-dev --no-progress --optimize-autoloader

@endtask


{{-- Assets task
-------------------------------------------------------------------
Copy the specified assets onto the server. If you specify an
entire directory, make sure the directory doesn't already exist,
otherwise you'll get unexpected results.
-------------------------------------------------------------------}}
@task('assets', ['on' => 'local'])

echo "Installing assets."
@foreach ($target->assets($release) as $sourcePath => $targetPath)
    echo "Copying {{ $sourcePath }}."
    scp -P{{ $target->port }} -rq "{{ $sourcePath }}" "{{ $targetPath }}"
@endforeach

@endtask


{{-- Database migrations
-------------------------------------------------------------------
You can migrate database chages automatically. However this
task is OFF by default as it could be potentially dangerous. You
can turn it on in the config.
-------------------------------------------------------------------}}
@task('database', ['on' => 'live'])

# Only run this if we are allowed to migrate
@if ($target->migrate)
    echo "Running database migrations."
    cd {{ $target->paths('releases', $release) }}
    {{ $target->artisan() }} migrate --force
@endif
@endtask


{{-- The "Make it live" task
-------------------------------------------------------------------
The final task is to make the deployment live by creating a
symlink to the new deployment from the "serve" path.
-------------------------------------------------------------------}}
@task('live', ['on' => 'live'])

echo "Going live."
ln -nfs {{ $target->paths('releases', $release) }} {{ $target->paths('serve') }}
cd {{ $target->paths('serve') }}
{{ $target->artisan() }} storage:link

@endtask


{{-- Clean up old deployments
-------------------------------------------------------------------
Clean up any old deployments that are still on the target.
We'll leave the previous one intact just in case you need it.
-------------------------------------------------------------------}}
@task('cleanup', ['on' => 'live'])

echo "Cleaning up."
cd {{ $target->paths('serve') }}
{{ $target->artisan() }} platoon:cleanup --keep=2

@endtask

{{-- Finish up
-------------------------------------------------------------------
Run the platoon:finish command and echo the new release name.
-------------------------------------------------------------------}}
@task('finish', ['on' => 'local'])

cd {{ $target->paths('serve') }}
{{ $target->artisan() }} platoon:finish
echo "Release {{ $release }} is now live."

@endtask


{{-- The "Deploy" story
-------------------------------------------------------------------
This story will run through all the individual deployment
-------------------------------------------------------------------}}
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
