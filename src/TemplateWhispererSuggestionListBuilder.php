<?php

namespace Drupal\template_whisperer;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of templates whisperer entities.
 *
 * @see \Drupal\template_whisperer\Entity\TemplateWhisperer
 */
final class TemplateWhispererSuggestionListBuilder extends ConfigEntityListBuilder {

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Template Whisperer Suggestion Usage.
   *
   * @var \Drupal\template_whisperer\TemplateWhispererSuggestionUsage
   */
  protected $twSuggestionUsage;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $instance                    = parent::createInstance($container, $entity_type);
    $instance->urlGenerator      = $container->get('url_generator');
    $instance->twSuggestionUsage = $container->get('template_whisperer.suggestion.usage');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['name'] = $this->t('Name');
    $header['suggestion'] = [
      'data' => $this->t('Suggestion'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['usage'] = $this->t('Used in');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    $row['name'] = [
      'data' => $entity->getName(),
      'class' => ['menu-label'],
    ];
    $row['suggestion'] = $entity->getSuggestion();

    $usage = $this->t('never');
    $usages = $this->twSuggestionUsage->countUsage($entity);
    if ($usages > 0) {
      $usage = $this->formatPlural($usages, '1 place', '@count places');
    }
    $url = $this->urlGenerator->generateFromRoute('entity.template_whisperer_suggestion.usage', ['template_whisperer_suggestion' => $entity->id()]);
    $row['usage']['data']['#markup'] = '<a href="' . $url . '">' . $usage . '</a>';

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No suggestion has currently been set.');
    return $build;
  }

}
