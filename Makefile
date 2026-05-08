APP_ID    = openviduintegration
PHPUNIT   = vendor/bin/phpunit

.PHONY: all install test lint clean

all: install

## Install PHP dependencies
install:
	composer install --no-interaction

## Run unit tests (no coverage)
test: install
	$(PHPUNIT) --no-coverage

## Run unit tests with HTML coverage report
coverage: install
	$(PHPUNIT)

## Remove generated artefacts
clean:
	rm -rf vendor/ .phpunit.cache/
