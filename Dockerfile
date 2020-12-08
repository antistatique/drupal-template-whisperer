ARG BASE_IMAGE_TAG=8.9
FROM wengerk/drupal-for-contrib:${BASE_IMAGE_TAG}

# Override the default template for PHPUnit testing.
COPY phpunit.xml /opt/drupal/web/phpunit.xml
