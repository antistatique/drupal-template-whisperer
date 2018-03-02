# Developing on Template Whisperer

* Issues should be filed at
https://www.drupal.org/project/issues/template_whisperer
* Pull requests can be made against
https://github.com/antistatique/drupal-template-whisperer/pulls

## 📦 Repositories

Drupal repo
  ```
  git remote add drupal \
  https://wengerk@git.drupal.org:project/template_whisperer.git
  ```

Github repo
  ```
  git remote add github \
  https://github.com/antistatique/drupal-template-whisperer.git
  ```

## 🔧 Prerequisites

First of all, you need to have the following tools installed globally
on your environment:

  * drush
  * Latest dev release of Drupal 8.x.

## 🏆 Tests

Template Whisperer use BrowserTestBase to test
web-based behaviors and interactions.

For tests you need a working database connection and for browser tests
your Drupal installation needs to be reachable via a web server.
Copy the phpunit config file:

  ```bash
  $ cd core
  $ cp phpunit.xml.dist phpunit.xml
  ```

You must provide `SIMPLETEST_BASE_URL`, Eg. `http://localhost`.
You must provide `SIMPLETEST_DB`,
Eg. `sqlite://localhost/build/template_whisperer.sqlite`.

Run the functional tests:

  ```bash
  # You must be on the drupal-root folder - usually /web.
  $ cd web
  $ SIMPLETEST_DB="sqlite://localhost//tmp/tw.sqlite" \
  SIMPLETEST_BASE_URL='http://d8.test' \
  ../vendor/bin/phpunit -c core \
  --group template_whisperer_ui
  ```

Debug using

  ```bash
  # You must be on the drupal-root folder - usually /web.
  $ cd web
  $ SIMPLETEST_DB="sqlite://localhost//tmp/tw.sqlite" \
  SIMPLETEST_BASE_URL='http://d8.test' \
  ../vendor/bin/phpunit -c core \
  --group template_whisperer_ui \
  --printer="\Drupal\Tests\Listeners\HtmlOutputPrinter" --stop-on-error
  ```

You must provide a `BROWSERTEST_OUTPUT_DIRECTORY`,
Eg. `/path/to/webroot/sites/simpletest/browser_output`.

## 🚔 Check Drupal coding standards & Drupal best practices

You need to run composer before using PHPCS. Then register the Drupal
and DrupalPractice Standard with PHPCS:

  ```bash
  $ ./vendor/bin/phpcs --config-set installed_paths \
  `pwd`/vendor/drupal/coder/coder_sniffer
  ```

### Command Line Usage

Check Drupal coding standards:

  ```bash
  ./vendor/bin/phpcs --standard=Drupal --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info,md \
  --ignore=*/vendor/*,*/node_modules/* ./
  ```

Check Drupal best practices:

  ```bash
  ./vendor/bin/phpcs --standard=DrupalPractice --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info,md \
  --ignore=*/vendor/*,*/node_modules/* ./
  ```

Automatically fix coding standards

  ```bash
  ./vendor/bin/phpcbf --standard=Drupal --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info \
  --ignore=*/vendor/*,*/node_modules/* ./
  ```

### Improve global code quality using PHPCPD & PHPMD

Add requirements if necessary using `composer`:

  ```bash
  composer require --dev 'phpmd/phpmd:^2.6' 'sebastian/phpcpd:^3.0'
  ```

Detect overcomplicated expressions & Unused parameters, methods, properties

  ```bash
  ./vendor/bin/phpmd ./web/modules/custom text ./phpmd.xml
  ```

Copy/Paste Detector

  ```bash
  ./vendor/bin/phpcpd ./web/modules/custom
  ```

### Enforce code standards with git hooks

Maintaining code quality by adding the custom post-commit hook to yours.

  ```bash
  cat ./scripts/hooks/post-commit >> ./.git/hooks/post-commit
  ```
