# Developing on Template Whisperer

* Issues should be filed at
https://www.drupal.org/project/issues/template_whisperer
* Pull requests can be made against
https://github.com/antistatique/drupal-template-whisperer/pulls

## 📦 Repositories

Drupal repo

  ```bash
  git remote add drupal \
  git@git.drupal.org:project/template_whisperer.git
  ```

Github repo

  ```bash
  git remote add github \
  git@github.com:antistatique/drupal-template-whisperer.git
  ```

## 🔧 Prerequisites

First of all, you need to have the following tools installed globally
on your environment:

  * drush
  * Latest dev release of Drupal 8.x.
  * docker
  * docker-compose
  
### Project bootstrap

Once run, you will be able to access to your fresh installed Drupal on `localhost::8888`.

    docker-compose build --pull --build-arg BASE_IMAGE_TAG=8.9 drupal
    (get a coffee, this will take some time...)
    docker-compose up --build -d drupal
    docker-compose exec -u www-data drupal drush site-install standard --db-url="mysql://drupal:drupal@db/drupal" --site-name=Example -y
    
    # You may be interesed by reseting the admin passowrd of your Docker and install the module using those cmd.
    docker-compose exec drupal drush user:password admin admin
    docker-compose exec drupal drush en template_whisperer

## 🏆 Tests

We use the [Drupal official Docker images](https://hub.docker.com/_/drupal/) to run testing on our project.

As those images does not support - for now (2020-06-13) - Drupal 9, we split up the `docker-compose.yml` file into
two separates services:

    - Drupal 8: `drupal-8` (PHP 7.2) using `db-drupal-8` (MariaDB 10.1) and PHPUnit 7
    - Drupal 9: `drupal-9`(PHP 7.3) using `db-drupal-9` (MariaDB 10.3.7) and PHPUnit 8

Run testing by stopping at first failure using the following command:

    docker-compose exec -u www-data drupal phpunit --group=template_whisperer --stop-on-failure --configuration=/var/www/html/phpunit.xml
    
## 🚔 Check Drupal coding standards & Drupal best practices

You need to run composer before using PHPCS. Then register the Drupal
and DrupalPractice Standard with PHPCS:

  ```bash
  ./vendor/bin/phpcs --config-set installed_paths \
  `pwd`/vendor/drupal/coder/coder_sniffer
  ```

### Command Line Usage

echo "\n🚔  \033[0;32mRunning Code Sniffer Drupal & DrupalPractice for /web/modules/custom ...\033[0m"
./vendor/bin/phpcs --config-set installed_paths `pwd`/vendor/drupal/coder/coder_sniffer
./vendor/bin/phpcs --standard=Drupal --colors --extensions=php,module,inc,install,test,profile,theme,css,info,txt --ignore=*/vendor/* --encoding=utf-8 ./
./vendor/bin/phpcs --standard=DrupalPractice --colors --extensions=php,module,inc,install,test,profile,theme,css,info,txt --ignore=*/vendor/* --encoding=utf-8 ./

echo "\n💩  \033[0;32mRunning PHP Mess Detector ...\033[0m"
./vendor/bin/phpmd ./ text ./phpmd.xml --suffixes php,module,inc,install,test,profile,theme,css,info,txt --exclude vendor

echo "\n🛂  \033[0;32mRunning PHP Copy/Paste Detector ...\033[0m"
./vendor/bin/phpcpd ./ --names=*.php,*.module,*.inc,*.install,*.test,*.profile,*.theme,*.css,*.info,*.txt --names-exclude=*.md,*.info.yml --progress --ansi --exclude=vendor

Check Drupal coding standards:

  ```bash
  ./vendor/bin/phpcs --standard=Drupal --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info,md \
  --ignore=*/vendor/*,*/node_modules/* --encoding=utf-8 ./
  ```

Check Drupal best practices:

  ```bash
  ./vendor/bin/phpcs --standard=DrupalPractice --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info,md \
  --ignore=*/vendor/*,*/node_modules/* --encoding=utf-8 ./
  ```

Automatically fix coding standards

  ```bash
  ./vendor/bin/phpcbf --standard=Drupal --colors \
  --extensions=php,module,inc,install,test,profile,theme,css,info \
  --ignore=*/vendor/*,*/node_modules/* --encoding=utf-8 ./
  ```

Checks compatibility with PHP interpreter versions

  ```bash
  ./vendor/bin/phpcf \
  --file-extensions php,module,inc,install,test,profile,theme,info \
  --exclude vendor ./
  ```

### Improve global code quality using PHPCPD & PHPMD

Add requirements if necessary using `composer`:

  ```bash
  composer require --dev 'phpmd/phpmd:^2.6' 'sebastian/phpcpd:^3.0' 'wapmorgan/php-code-fixer:^2.0'
  ```

Detect overcomplicated expressions & Unused parameters, methods, properties

  ```bash
  ./vendor/bin/phpmd ./web/modules/custom text ./phpmd.xml
  ```

Copy/Paste Detector

  ```bash
  ./vendor/bin/phpcpd ./web/modules/custom
  ```

PhpCodeFixer

  A scanner that checks compatibility of your code with new interpreter versions.

  ```bash
  ./vendor/bin/phpcf ./web/modules/custom
  ```

### Enforce code standards with git hooks

Maintaining code quality by adding the custom post-commit hook to yours.

  ```bash
  cat ./scripts/hooks/post-commit >> ./.git/hooks/post-commit
  ```
