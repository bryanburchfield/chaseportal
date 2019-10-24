#!/bin/sh

# activate maintenance mode
php artisan down

# update source code
git pull

# update PHP dependencies
export COMPOSER_HOME='/tmp/composer'
composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader
	# --no-interaction	    Do not ask any interactive question
	# --no-dev		        Disables installation of require-dev packages.
	# --prefer-dist		    Forces installation from package dist even for dev versions.
    # --optimize-autoloader Optimize autoload files

# update database
php artisan migrate --force
	# --force		Required to run when in production.

# clear caches
php artisan cache:clear

# Clear expired password reset tokens
php artisan auth:clear-resets

# recache everything - 'optimize' caches config and routes
php artisan optimize
php artisan view:cache

# restart queues
php artisan -v queue:restart

# make sure anything in storage is writeable
find storage -not -path '*/\.*' -exec chmod ug+rwx {} \;

# stop maintenance mode
php artisan up
