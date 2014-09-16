PHP=`which php`

composer.phar:
	echo "Downloading composer"
	$(PHP) -r "readfile('https://getcomposer.org/installer');" | $(PHP)

build: composer.phar
	php composer.phar dump-autoload

test:
	phpunit
