<?php

namespace Drupal\Tests\template_whisperer\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\template_whisperer\Traits\InvokeMethodTrait;

/**
 * Tests the Template Whisperer Manager.
 *
 * @group template_whisperer_kernel
 * @group template_whisperer
 */
class TemplateWhispererManagerTest extends KernelTestBase {
  use InvokeMethodTrait;

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

    // Since Drupal 10.2.0 installing the table sequences with the
    // method KernelTestBase::installSchema() is deprecated.
    if (version_compare(\Drupal::VERSION, '10.2.0', '<')) {
      $this->installSchema('system', ['sequences']);
    }

    // Set default storage backend and configure the theme system.
    $this->installConfig(['field', 'system']);
    $this->installEntitySchema('template_whisperer_suggestion');
    $this->installSchema('template_whisperer', ['template_whisperer_suggestion_usage']);

    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    // Create 1 default Template Whisperer.
    $this->entityTypeManager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'foo',
      'name'       => 'Foo',
      'suggestion' => 'foo',
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
   * @covers \Drupal\template_whisperer\TemplateWhispererManager::getFieldSuggestions
   */
  public function testGetFieldSuggestions() {
    /** @var \Drupal\template_whisperer\TemplateWhispererManager $tw_manager */
    $tw_manager = \Drupal::service('plugin.manager.template_whisperer');

    // Set an existing template whisperer.
    $entity = $this->entityTypeManager->getStorage('entity_test')->create([
      'field_template_whisperer' => 'foo',
    ]);
    $entity->save();

    // Ensure it does return the proper foo value.
    $suggestions = $this->invokeMethod($tw_manager, 'getFieldSuggestions', [
      $entity,
      'field_template_whisperer',
    ]);
    $this->assertEquals('foo', $suggestions);

    // Set an none-existing template whisperer.
    // This may happend when an whisperer is deleted but stay on node data.
    $entity = $this->entityTypeManager->getStorage('entity_test')->create([
      'field_template_whisperer' => 'bar',
    ]);
    $entity->save();

    // Ensure it does not return anything.
    $suggestions = $this->invokeMethod($tw_manager, 'getFieldSuggestions', [
      $entity,
      'field_template_whisperer',
    ]);
    $this->assertEmpty($suggestions);
  }

}
