<?php

namespace Drupal\template_whisperer\TwigExtension;

use Drupal\template_whisperer\TemplateWhispererManager;
use Drupal\template_whisperer\TemplateWhispererSuggestionUsage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Template Whisperer Twig extensions.
 */
final class TwigExtension extends AbstractExtension {

  /**
   * Constructs \Drupal\template_whisperer\TwigExtension\TwigExtension.
   *
   * @param \Drupal\template_whisperer\TemplateWhispererManager $twManager
   *   The template whisperer manager.
   * @param \Drupal\template_whisperer\TemplateWhispererSuggestionUsage $twSuggestionUsage
   *   The template whisperer suggestion usage service.
   */
  public function __construct(protected TemplateWhispererManager $twManager, protected TemplateWhispererSuggestionUsage $twSuggestionUsage) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('tw_suggestion_entities', $this->getEntitiesFromSuggestion(...)),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'template_whisperer.twig.extensions';
  }

  /**
   * Get the template whisperer entities corresponding to a suggestion.
   *
   * @param string $tw_suggestion
   *   The template whisperer suggestion.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface[]
   *   A collection of Template whisperer entities.
   */
  public function getEntitiesFromSuggestion(string $tw_suggestion) {
    $suggestion = $this->twManager->getOneBySuggestion($tw_suggestion);
    return $suggestion ? $this->twSuggestionUsage->listUsage($suggestion) : [];
  }

}
