APP_ID    = openviduintegration
PHPUNIT   = vendor/bin/phpunit

.PHONY: all install test lint coverage clean package clean-package

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

## PHP syntax lint – runs php -l on every lib/ and tests/ file
lint:
	find lib tests -name "*.php" -print0 | xargs -0 -n1 php -l

## Package the app for distribution (requires composer to be available)
package: clean-package
	composer install --no-dev --optimize-autoloader --no-interaction
	mkdir -p build/$(APP_ID)
	rsync -a \
		--exclude='build' \
		--exclude='tests' \
		--exclude='.git' \
		--exclude='.github' \
		--exclude='.gitignore' \
		--exclude='phpunit.xml' \
		--exclude='Makefile' \
		--exclude='CHANGELOG.md' \
		. build/$(APP_ID)/
	cd build && zip -r $(APP_ID).zip $(APP_ID)/

clean-package:
	rm -rf build/

## Remove generated artefacts
clean:
	rm -rf vendor/ .phpunit.cache/
