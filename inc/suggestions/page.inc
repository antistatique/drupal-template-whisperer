<?php

/**
 * @file
 * Contains function to generate suggestions on the page level.
 */

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Method used in the template_whisperer_theme_suggestions_alter().
 *
 * Generate additional suggestions based on the page.
 *   - page--node--1--suggestion.html.twig
 *   - page--node--suggestion.html.twig.
 */
function _template_whisperer_theme_suggestions_page(array &$suggestions, array $variables, $hook) {
  if ($hook !== 'page') {
    return;
  }

  // Get the entity from the Route parameters.
  $entity = NULL;
  foreach (\Drupal::routeMatch()->getParameters() as $param) {
    if ($param instanceof ContentEntityInterface) {
      $entity = $param;
      break;
    }
  }

  if (!$entity) {
    return;
  }

  $twManager = \Drupal::service('plugin.manager.template_whisperer');
  $suggestionsEntity = $twManager->suggestionsFromEntity($entity);

  if (empty($suggestionsEntity)) {
    return;
  }

  // Expose one suggestion by fields.
  foreach ($suggestionsEntity as $suggestion) {
    // Collect every suggestions variants that will be injected.
    $base_suggestions = [];

    // Will suggest page--node--1--suggestion.html.twig.
    $base_suggestions[] = [
      $hook,
      $entity->getEntityTypeId(),
      $entity->id(),
    ];

    // Will suggest page--node--suggestion.html.twig.
    $base_suggestions[] = [
      $hook,
      $entity->getEntityTypeId(),
    ];

    // Generate suggestions for every variations.
    foreach ($base_suggestions as $base_suggestion) {
      $suggestions[] = implode('__', $base_suggestion) . '__' . $suggestion;
    }
  }
}
