@servers(['web' => 'deployer@206.189.216.247'])

@setup
$repository = 'git@gitlab.com:vct.aragao/Sistema-de-gestao-forro.git';
$releases_dir = '/var/www/sigf/releases';
$app_dir = '/var/www/sigf';
$release = date('YmdHis');
$new_release_dir = $releases_dir .'/'. $release;
@endsetup

@story('deploy')
setup_app_dir
clone_repository
run_composer
create_storage
create_env
create_links
@endstory

@task('setup_app_dir')
echo 'Checking app dir'
[ -d {{ $app_dir }} ] && echo "Exists" || mkdir {{ $app_dir }}
@endtask

@task('clone_repository')
echo 'Cloning repository'
[ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
git clone --depth 1 {{ $repository }} {{ $new_release_dir }}
cd {{ $new_release_dir }}
git reset --hard {{ $commit }}
@endtask

@task('run_composer')
echo "Starting deployment ({{ $release }})"
cd {{ $new_release_dir }}
composer install --prefer-dist --no-scripts -q -o
@endtask

@task('create_storage')
if [ -d {{ $app_dir }}/storage ]
then
if [ -d {{ $app_dir }}/storage/framework]
then
if ! [ -d {{ $app_dir }}/storage/framework/cache]
then
mkdir {{ $app_dir }}/storage/framework/cache
fi
if ! [ -d {{ $app_dir }}/storage/framework/sessions]
then
mkdir {{ $app_dir }}/storage/framework/sessions
fi
if ! [ -d {{ $app_dir }}/storage/framework/sessions]
then
mkdir {{ $app_dir }}/storage/framework/views
fi
else
mkdir {{ $app_dir }}/storage/framework
mkdir {{ $app_dir }}/storage/framework/cache
mkdir {{ $app_dir }}/storage/framework/sessions
mkdir {{ $app_dir }}/storage/framework/cache
mkdir {{ $app_dir }}/storage/framework/views
fi
else
mkdir {{ $app_dir }}/storage
mkdir {{ $app_dir }}/storage/framework
mkdir {{ $app_dir }}/storage/framework/cache
mkdir {{ $app_dir }}/storage/framework/sessions
mkdir {{ $app_dir }}/storage/framework/cache
mkdir {{ $app_dir }}/storage/framework/views
fi
@endtask

@task('create_env')
echo "Creating .env file"
[ -f {{ $app_dir }}/.env ] || touch {{ $app_dir }}/.env
@endtask

@task('create_links')
echo "Linking storage directory"
ln -nfs {{ $app_dir }}/storage {{ $new_release_dir }}/storage

echo 'Linking .env file'
ln -nfs {{ $app_dir }}/.env {{ $new_release_dir }}/.env

echo 'Linking current release'
ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
@endtask