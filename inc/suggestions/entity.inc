<?php

/**
 * @file
 * Contains function to generate suggestions on the page level.
 */

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Method used in the template_whisperer_theme_suggestions_alter().
 *
 * Generate additional suggestions based on the entity.
 *   - node--1--article--teaser--suggestion.html.twig
 *   - node--article--teaser--suggestion.html.twig
 *   - node--1--article--suggestion.html.twig
 *   - node--article--suggestion.html.twig.
 */
function _template_whisperer_theme_suggestions_entity(array &$suggestions, array $variables, $hook) {
  if (isset($variables['elements']['#entity_type']) && $variables['elements']['#entity_type'] === $hook) {
    $twManager = \Drupal::service('plugin.manager.template_whisperer');

    $entity = NULL;
    if (isset($variables['elements']['#' . $hook])) {
      $entity = $variables['elements']['#' . $hook];
    }

    if (!empty($entity) && $entity instanceof ContentEntityInterface) {
      $suggestionsEntity = $twManager->suggestionsFromEntity($entity);

      if (!empty($suggestionsEntity)) {

        // Expose one suggestion by fields.
        foreach ($suggestionsEntity as $suggestion) {
          // Collect every suggestions variants that will be injected.
          $base_suggestions = [];

          // Will suggest node--article--suggestion.html.twig.
          $base_suggestions[] = [
            $entity->getEntityTypeId(),
            $entity->bundle(),
          ];

          // Will suggest node--1--article--suggestion.html.twig.
          $base_suggestions[] = [
            $entity->getEntityTypeId(),
            $entity->id(),
            $entity->bundle(),
          ];

          // Colect variation based on the view_mode.
          if (isset($variables['elements']['#view_mode'])) {
            // Will suggest node--article--teaser--suggestion.html.twig.
            $base_suggestions[] = [
              $entity->getEntityTypeId(),
              $entity->bundle(),
              $variables['elements']['#view_mode'],
            ];
            // Will suggest node--1--article--teaser--suggestion.html.twig.
            $base_suggestions[] = [
              $entity->getEntityTypeId(),
              $entity->id(),
              $entity->bundle(),
              $variables['elements']['#view_mode'],
            ];
          }

          // Generate suggestions for every variations.
          foreach ($base_suggestions as $base_suggestion) {
            $suggestions[] = implode('__', $base_suggestion) . '__' . $suggestion;
          }
        }
      }
    }
  }
}
