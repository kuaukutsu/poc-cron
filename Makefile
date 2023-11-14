PHP_VERSION ?= 8.1
USER = $$(id -u)

composer:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer install --ignore-platform-req=ext-pcntl

composer-up:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer update --ignore-platform-req=ext-pcntl

composer-dump:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer dump-autoload

cli:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app/src \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		sh

run:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app/tests \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		php run.php

psalm:
	docker run --init -it --rm -v "$$(pwd):/app" -e XDG_CACHE_HOME=/tmp -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/psalm

phpunit:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpunit

phpcs:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpcs

phpcbf:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpcbf

rector:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/rector
