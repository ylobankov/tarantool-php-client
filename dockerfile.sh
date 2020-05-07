#!/usr/bin/env bash

if [[ -z "$PHP_IMAGE" ]]; then
    PHP_IMAGE='php:7.4-cli'
fi

if [[ -z "$TNT_PACKER" ]]; then
    TNT_PACKER='pure'
fi

if [[ -z "$TNT_LISTEN_URI" ]]; then
    TNT_LISTEN_URI='tarantool:3301'
fi

RUN_CMDS=''
if [[ -n "$COVERAGE_FILE" ]]; then
    RUN_CMDS="$RUN_CMDS && \\\\\n    pecl install pcov && docker-php-ext-enable pcov"
fi

if [[ $TNT_PACKER == pecl ]]; then
  PHPUNIT_EXCLUDE_GROUP='only-pure-packer'
else
  PHPUNIT_EXCLUDE_GROUP='only-pecl-packer'
fi

echo -e "
FROM $PHP_IMAGE

RUN apt-get update && \\
    apt-get install -y curl git libmpdec-dev unzip && \\
    docker-php-ext-install sockets && \\
    pecl install msgpack && docker-php-ext-enable msgpack && \\
    pecl install decimal && docker-php-ext-enable decimal${RUN_CMDS}

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV PATH=~/.composer/vendor/bin:\$PATH
ENV TNT_PACKER=$TNT_PACKER TNT_LISTEN_URI=$TNT_LISTEN_URI

CMD if [ ! -f composer.lock ]; then composer install; fi && \\
    vendor/bin/phpunit --exclude-group ${PHPUNIT_EXCLUDE_GROUP} ${COVERAGE_FILE:+ --coverage-text --coverage-clover=}$COVERAGE_FILE
"
