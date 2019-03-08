<?php

namespace Drupal\template_whisperer;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of templates whisperer entities.
 *
 * @see \Drupal\template_whisperer\Entity\TemplateWhisperer
 */
class TemplateWhispererSuggestionListBuilder extends ConfigEntityListBuilder {

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
   * Constructs a TemplateWhispererSuggestionListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   * @param \Drupal\template_whisperer\TemplateWhispererSuggestionUsage $tw_suggestion_usage
   *   Template Whisperer Suggestion Usage.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator, TemplateWhispererSuggestionUsage $tw_suggestion_usage) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator      = $url_generator;
    $this->twSuggestionUsage = $tw_suggestion_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator'),
      $container->get('template_whisperer.suggestion.usage')
    );
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
