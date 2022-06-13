@include ('./vendor/autoload.php')

{{-- Platoon Deployment Script
-------------------------------------------------------------------
This Envoy script is part of the Platoon deployment toolset.
You can edit this file if you want, but your Platoon
installation will overwrite it if you make changes later.
-------------------------------------------------------------------}}
@setup

$helper = new TPG\Platoon\Helpers\Envoy;

$release = $helper->newRelease();

$target = $helper->target($server);

@endsetup

@servers(['local' => $helper->localhost, 'live' => $target->hostString])

{{-- Installation task
-------------------------------------------------------------------
This task gets the software onto the server. It will clone the
repo and link the .env file and storage directory.
-------------------------------------------------------------------}}
@task('install', ['on' => 'live'])

[[ ! -d "{{ $target->paths('releases') }}" ]] && mkdir {{ $target->paths('releases') }}

cd {{ $target->paths('releases') }}
git clone --depth 50 -b {{ $target->branch }} "{{ $helper->repo() }}" {{ $release }}
rm -rf {{ $target->paths('releases', $release) }}/storage
rm -f {{ $target->paths('releases', $release) }}/.env
ln -nfs {{ $target->path }}/.env {{ $target->paths('releases', $release) }}/.env
ln -nfs {{ $target->path }}/storage {{ $target->paths('releases', $release) }}/storage

@endtask

{{-- Prep task
-------------------------------------------------------------------
Prepare the target directory for the project.
-------------------------------------------------------------------}}
@task('prep', ['on' => 'live'])

cd {{ $target->path }}
if [[ ! -d "{{ $target->paths('storage') }}" ]]
then
    cp -r {{ $target->paths('releases', $release) }}/storage {{ $target->path }}/storage
    cp {{ $target->paths('releases', $release) }}/.env.example {{ $target->path }}/.env
fi

@endtask

{{-- Composer dependencies task
-------------------------------------------------------------------
This task gets the software onto the server. It will clone the
repo and link the .env file and storage directory.
-------------------------------------------------------------------}}
@task('dependencies', ['on' => 'live'])

cd {{ $target->paths('releases', $release) }}
{{ $target->composer() }} self-update
{{ $target->composer() }} install --prefer-dist --no-dev --no-progress

@endtask

{{-- Assets task
-------------------------------------------------------------------
Copy the specified assets onto the server. If you specify an
entire directory, make sure the directory doesn't already exist,
otherwise you'll get unexpected results.
-------------------------------------------------------------------}}
@task('assets', ['on' => 'live'])

@foreach ($target->assets() as $sourcePath => $targetPath)
    scp -P{{ $target->port }} -rq {{ $sourcePath }} {{ $targetPath }}
@endforeach

@endtask

{{-- Database migrations
-------------------------------------------------------------------
You can migrate database chages automatically. However this
task is OFF by default as it could be potentially dangerous. You
can turn it on in the config.
-------------------------------------------------------------------}}
@task('database', ['on' => 'live'])
@if ($target->migrate)
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

ln -nfs {{ $target->paths('releases', $release) }} {{ $target->paths('serve') }}
cd {{ $target->paths('serve') }}
{{ $target->artisan() }} storage:link
{{ $target->artisan() }} horizon:publish

@endtask

{{-- The "Deploy" story
-------------------------------------------------------------------
This story will run through all the individual deployment
-------------------------------------------------------------------}}
@story('deploy')
    install
    prep
    dependencies
    database
    live
@endstory
