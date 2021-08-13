<?php

namespace Drupal\Tests\template_whisperer\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface;

/**
 * Tests the Template Whisperer twig extensions.
 *
 * @group template_whisperer_twig
 * @group template_whisperer
 */
class TwigExtensionTest extends KernelTestBase {

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'entity_test',
    'template_whisperer',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');
    $this->installSchema('system', ['sequences']);

    // Set default storage backend and configure the theme system.
    $this->installConfig(['field', 'system']);
    $this->installEntitySchema('template_whisperer_suggestion');
    $this->installSchema('template_whisperer', ['template_whisperer_suggestion_usage']);

    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    // Create 2 defaults Template Whisperer.
    $this->entityTypeManager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'foo',
      'name'       => 'Foo',
      'suggestion' => 'foo',
    ])->save();
    $this->entityTypeManager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'bar',
      'name'       => 'Bar',
      'suggestion' => 'bar',
    ])->save();

    // Create the Template Whisperer field.
    // And attach it to the entity_test.
    $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->create([
        'field_name'  => 'field_template_whisperer',
        'entity_type' => 'entity_test',
        'type'        => 'template_whisperer',
      ])->save();

    $this->entityTypeManager
      ->getStorage('field_config')
      ->create([
        'entity_type' => 'entity_test',
        'field_name'  => 'field_template_whisperer',
        'bundle'      => 'entity_test',
      ])->save();

    // Create 4 entities using template whisperer.
    $entity = $this->entityTypeManager->getStorage('entity_test')->create([]);
    $entity->save();
    $entity = $this->entityTypeManager->getStorage('entity_test')->create([
      'field_template_whisperer' => 'bar',
    ]);
    $entity->save();
    $entity = $this->entityTypeManager->getStorage('entity_test')->create([
      'field_template_whisperer' => 'foo',
    ]);
    $entity->save();
    $entity = $this->entityTypeManager->getStorage('entity_test')->create([
      'field_template_whisperer' => 'foo',
    ]);
    $entity->save();
  }

  /**
   * @covers Drupal\template_whisperer\TwigExtension\TwigExtension::getEntitiesFromSuggestion
   */
  public function testsGetOneEntityFromSuggestion() {
    /** @var \Drupal\template_whisperer\TwigExtension\TwigExtension $extension */
    $extension = \Drupal::service('template_whisperer.twig.extension');

    $suggestions = $extension->getEntitiesFromSuggestion('bar');
    $this->assertIsArray($suggestions);
    $this->assertCount(1, $suggestions);
    $this->containsOnlyInstancesOf(TemplateWhispererSuggestionEntityInterface::class);
  }

  /**
   * @covers Drupal\template_whisperer\TwigExtension\TwigExtension::getEntitiesFromSuggestion
   */
  public function testsGetEntitiesFromSuggestion() {
    /** @var \Drupal\template_whisperer\TwigExtension\TwigExtension $extension */
    $extension = \Drupal::service('template_whisperer.twig.extension');
    $suggestions = $extension->getEntitiesFromSuggestion('foo');
    $this->assertIsArray($suggestions);
    $this->assertCount(2, $suggestions);
    $this->containsOnlyInstancesOf(TemplateWhispererSuggestionEntityInterface::class);
  }

  /**
   * @covers Drupal\template_whisperer\TwigExtension\TwigExtension::getEntitiesFromSuggestion
   */
  public function testsGetNoneEntitiesBySuggestion() {
    /** @var \Drupal\template_whisperer\TwigExtension\TwigExtension $extension */
    $extension = \Drupal::service('template_whisperer.twig.extension');
    $suggestions = $extension->getEntitiesFromSuggestion($this->randomString(16));
    $this->assertIsArray($suggestions);
    $this->assertEmpty($suggestions);
  }

}
