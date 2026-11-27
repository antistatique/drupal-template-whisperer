<?php

namespace Drupal\template_whisperer\TwigExtension;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\RendererInterface;
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
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(protected TemplateWhispererManager $twManager, protected TemplateWhispererSuggestionUsage $twSuggestionUsage, protected RendererInterface $renderer) {
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
   * The cacheability of the suggestion usage is bubbled to the current render
   * context, so the render is invalidated when an entity starts or stops using
   * the suggestion.
   *
   * @param string $tw_suggestion
   *   The template whisperer suggestion.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface[]
   *   A collection of Template whisperer entities.
   */
  public function getEntitiesFromSuggestion(string $tw_suggestion) {
    $suggestion = $this->twManager->getOneBySuggestion($tw_suggestion);

    $usages = $suggestion ? $this->twSuggestionUsage->listUsage($suggestion) : [];

    // A suggestion nothing declares yet still gets its usage cache tag, so the
    // render is invalidated once the suggestion is created and used.
    $cacheability = $suggestion
      ? $this->twSuggestionUsage->getUsageCacheability($suggestion, $usages)
      : (new CacheableMetadata())->setCacheTags($this->twSuggestionUsage->getCacheTags($tw_suggestion));
    $this->bubbleCacheability($cacheability);

    return $usages;
  }

  /**
   * Bubbles a cacheability to the current render context, when there is one.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheability
   *   The cacheability to bubble.
   */
  private function bubbleCacheability(CacheableMetadata $cacheability): void {
    if (!$this->renderer->hasRenderContext()) {
      return;
    }

    $build = [];
    $cacheability->applyTo($build);
    $this->renderer->render($build);
  }

}
