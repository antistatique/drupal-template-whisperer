<?php

namespace Drupal\template_whisperer\Hook;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\template_whisperer\TemplateWhispererManager;

/**
 * Hook implementations generating the Template Whisperer theme suggestions.
 */
final class SuggestionsHook {

  /**
   * Constructs a new SuggestionsHook object.
   *
   * @param \Drupal\template_whisperer\TemplateWhispererManager $twManager
   *   The Template Whisperer manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(
    protected TemplateWhispererManager $twManager,
    protected RouteMatchInterface $routeMatch,
  ) {
  }

  /**
   * Implements hook_theme_suggestions_alter().
   */
  #[Hook('theme_suggestions_alter')]
  public function themeSuggestionsAlter(array &$suggestions, array $variables, $hook): void {
    $this->themeSuggestionsPage($suggestions, $variables, $hook);
    $this->themeSuggestionsEntity($suggestions, $variables, $hook);
  }

  /**
   * Generate additional suggestions based on the page.
   *
   *   - page--node--1--suggestion.html.twig
   *   - page--node--suggestion.html.twig.
   */
  protected function themeSuggestionsPage(array &$suggestions, array $variables, $hook): void {
    if ($hook !== 'page') {
      return;
    }

    // Get the entity from the Route parameters.
    $entity = NULL;
    foreach ($this->routeMatch->getParameters() as $param) {
      if ($param instanceof ContentEntityInterface) {
        $entity = $param;
        break;
      }
    }

    if (!$entity) {
      return;
    }

    $suggestionsEntity = $this->twManager->suggestionsFromEntity($entity);

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

  /**
   * Generate additional suggestions based on the entity.
   *
   *   - node--1--article--teaser--suggestion.html.twig
   *   - node--article--teaser--suggestion.html.twig
   *   - node--1--article--suggestion.html.twig
   *   - node--article--suggestion.html.twig.
   */
  protected function themeSuggestionsEntity(array &$suggestions, array $variables, $hook): void {
    if (isset($variables['elements']['#entity_type']) && $variables['elements']['#entity_type'] === $hook) {
      $entity = NULL;
      if (isset($variables['elements']['#' . $hook])) {
        $entity = $variables['elements']['#' . $hook];
      }

      if (!empty($entity) && $entity instanceof ContentEntityInterface) {
        $suggestionsEntity = $this->twManager->suggestionsFromEntity($entity);

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

}
