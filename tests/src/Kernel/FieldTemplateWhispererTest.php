<?php

namespace Drupal\Tests\template_whisperer\Kernel;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the Template Whisperer field type.
 *
 * This class is based off the tests used in Drupal core for field plugins,
 * since we need to use some of the same convenience methods for testing
 * our custom field type.
 *
 * @see \Drupal\KernelTests\KernelTestBase
 *
 * @group template_whisperer_kernel_page
 * @group template_whisperer_kernel
 * @group template_whisperer
 */
class FieldTemplateWhispererTest extends FieldKernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'template_whisperer'];

  /**
   * Collection of  Template Whisperer test entites.
   *
   * @var Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity[]
   */
  private $suggestions;

  /**
   * {@inheritdoc}
   *
   * This sets up the entity_test and user types to use the field plugins.
   */
  protected function setUp() {
    parent::setUp();
    $type_manager = $this->container->get('entity_type.manager');

    // Create a default Template Whisperer.
    // Set up our entity_type and user type for the field.
    $this->suggestions['foo'] = $type_manager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'foo',
      'name'       => 'Foo',
      'suggestion' => 'foo',
    ]);
    $this->suggestions['bar'] = $type_manager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'bar',
      'name'       => 'Bar',
      'suggestion' => 'bar',
    ]);

    // Set up our entity_type and user type for the field.
    $type_manager
      ->getStorage('field_storage_config')
      ->create([
        'field_name'  => 'field_template_whisperer',
        'entity_type' => 'entity_test',
        'type'        => 'template_whisperer',
      ])->save();

    $type_manager
      ->getStorage('field_config')
      ->create([
        'entity_type' => 'entity_test',
        'field_name'  => 'field_template_whisperer',
        'bundle'      => 'entity_test',
      ])->save();

    // Create a form display for the default form mode, and
    // add the field type.
    $type_manager
      ->getStorage('entity_form_display')
      ->create([
        'targetEntityType' => 'entity_test',
        'bundle'           => 'entity_test',
        'mode'             => 'default',
        'status'           => TRUE,
      ])
      ->setComponent('field_template_whisperer', [
        'type' => 'template_whisperer',
      ])
      ->save();
  }

  /**
   * Test entity fields of the template_whisperer field type.
   */
  public function testTemaplteWhispererFieldItem() {
    // Verify entity creation.
    $type_manager = $this->container->get('entity_type.manager');
    $entity = $type_manager->getStorage('entity_test')->create([]);

    $target_id = $this->suggestions['foo']->getSuggestion();
    $entity->field_template_whisperer->target_id = $target_id;
    $entity->save();

    // Verify entity has been created properly.
    $id = $entity->id();
    $entity = $type_manager->getStorage('entity_test')->load($id);

    $this->assertTrue($entity->field_template_whisperer instanceof FieldItemListInterface, 'Field implements interface.');
    $this->assertTrue($entity->field_template_whisperer[0] instanceof FieldItemInterface, 'Field item implements interface.');
    $this->assertEqual($entity->field_template_whisperer->target_id, $target_id);
    $this->assertEqual($entity->field_template_whisperer[0]->target_id, $target_id);

    // Verify changing the field's value.
    $new_target_id = $this->suggestions['bar']->getSuggestion();
    $entity->field_template_whisperer->value = $new_target_id;
    $this->assertEqual($entity->field_template_whisperer->value, $new_target_id);

    // Read changed entity and assert changed values.
    $entity->save();
    $entity = $type_manager->getStorage('entity_test')->load($id);
    $this->assertEqual($entity->field_template_whisperer->target_id, $target_id);
    $this->assertEqual($entity->field_template_whisperer[0]->target_id, $target_id);
  }

  /**
   * Test multiple access scenarios for the template whisperer field.
   *
   * @dataProvider providerTestTemaplteWhispererFieldAccess
   */
  public function testTemaplteWhispererFieldAccess($permissions, $scenarios) {
    // Create and entity with a template whisperer value.
    $type_manager = $this->container->get('entity_type.manager');
    $entity = $type_manager->getStorage('entity_test')->create([]);
    $target_id = $this->suggestions['foo']->getSuggestion();
    $entity->field_template_whisperer->target_id = $target_id;
    $entity->save();

    $test_user = $this->createUser($permissions);

    foreach ($scenarios as $operation => $expected) {
      $result = $entity->field_template_whisperer->access($operation, $test_user);
      $this->assertEqual($result, $expected);
    }
  }

  /**
   * Data provider for testTemaplteWhispererFieldAccess.
   *
   * @return array
   *   Nested array of testing data. Arranged like this:
   *   - Array of permissions for the user.
   *   - Scenarios with expected access results.
   */
  public function providerTestTemaplteWhispererFieldAccess() {
    return [
      [
        // Admin access.
        ['bypass node access'],
        ['edit' => TRUE],
      ],
      [
        // Low access.
        ['view test entity'],
        ['edit' => FALSE],
      ],
      [
        // Edit access.
        ['administer the template whisperer field'],
        ['edit' => TRUE],
      ],
      [
        // No access.
        [],
        ['edit' => FALSE],
      ],
    ];
  }

}
