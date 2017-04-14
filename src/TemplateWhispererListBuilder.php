<?php

namespace Drupal\template_whisperer;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\Query\QueryFactory;
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
class TemplateWhispererListBuilder extends ConfigEntityListBuilder {

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a TemplateWhispererListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UrlGeneratorInterface $url_generator, QueryFactory $query_factory) {
    parent::__construct($entity_type, $storage);
    $this->urlGenerator = $url_generator;
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('url_generator'),
      $container->get('entity.query')
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

    // @TODO: Usage listing + Usage page
    /** @var \Drupal\image\Entity\ImageStyle $image_style */
    $usage = 0;
    // Foreach ($image_styles as $image_style) {
    //   if (count($usage) < 2) {
    //     $usage[] = $image_style->link();
    //   }
    // }
    //
    // $other_image_styles = array_splice($image_styles, 2);
    // if ($other_image_styles) {
    //   $usage_message = t('@first, @second and @count more', [
    //     '@first' => $usage[0],
    //     '@second' => $usage[1],
    //     '@count' => count($other_image_styles),
    //   ]);
    // }
    // else {
    //   $usage_message = implode(', ', $usage);
    // }.
    $row['usage']['data']['#markup'] = $usage;

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
