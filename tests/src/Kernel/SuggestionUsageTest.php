<?php

namespace Drupal\Tests\template_whisperer\Kernel;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\template_whisperer\Plugin\Field\FieldType\TemplateWhispererFieldItem;
use Drupal\template_whisperer\Plugin\Field\FieldType\TemplateWhispererFieldItemList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the suggestion usage maintained by the Template Whisperer field.
 *
 * @group template_whisperer
 */
#[CoversClass(TemplateWhispererFieldItem::class)]
#[CoversClass(TemplateWhispererFieldItemList::class)]
#[Group('template_whisperer_kernel')]
#[Group('template_whisperer')]
#[RunTestsInSeparateProcesses]
class SuggestionUsageTest extends KernelTestBase {

  /**
   * {@inheritdoc}
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
    $this->installConfig(['field', 'system']);
    $this->installEntitySchema('template_whisperer_suggestion');
    $this->installSchema('template_whisperer', ['template_whisperer_suggestion_usage']);

    $entity_type_manager = $this->container->get('entity_type.manager');

    foreach (['foo', 'bar'] as $suggestion) {
      $entity_type_manager->getStorage('template_whisperer_suggestion')->create([
        'id'         => $suggestion,
        'name'       => ucfirst($suggestion),
        'suggestion' => $suggestion,
      ])->save();
    }

    $entity_type_manager->getStorage('field_storage_config')->create([
      'field_name'  => 'field_template_whisperer',
      'entity_type' => 'entity_test',
      'type'        => 'template_whisperer',
    ])->save();
    $entity_type_manager->getStorage('field_config')->create([
      'entity_type' => 'entity_test',
      'field_name'  => 'field_template_whisperer',
      'bundle'      => 'entity_test',
    ])->save();
  }

  /**
   * Creates and saves a test entity using a suggestion.
   *
   * @param string $suggestion
   *   The suggestion the entity uses.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The saved entity.
   */
  protected function createEntityUsing(string $suggestion): ContentEntityInterface {
    $entity = $this->container->get('entity_type.manager')->getStorage('entity_test')->create([
      'field_template_whisperer' => $suggestion,
    ]);
    $entity->save();

    return $entity;
  }

  /**
   * Tests the usage follows the suggestion set on an entity.
   */
  public function testUsageFollowsTheFieldValue(): void {
    $storage = $this->container->get('entity_type.manager')->getStorage('template_whisperer_suggestion');
    $usage = $this->container->get('template_whisperer.suggestion.usage');

    $entity = $this->createEntityUsing('foo');
    $this->assertSame(1, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(0, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');

    // Saving without changing the suggestion.
    $entity->set('name', 'renamed')->save();
    $this->assertSame(1, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(0, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');

    // Switching from a suggestion to another one.
    $entity->set('field_template_whisperer', 'bar')->save();
    $this->assertSame(0, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(1, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');

    // Deleting the entity.
    $entity->delete();
    $this->assertSame(0, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(0, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');
  }

  /**
   * Tests the usage is released when the field is emptied.
   */
  public function testUsageIsReleasedWhenTheFieldIsEmptied(): void {
    $storage = $this->container->get('entity_type.manager')->getStorage('template_whisperer_suggestion');
    $usage = $this->container->get('template_whisperer.suggestion.usage');

    $entity = $this->createEntityUsing('foo');
    $this->createEntityUsing('foo');
    $this->assertSame(2, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(0, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');

    $entity->set('field_template_whisperer', NULL)->save();
    $this->assertSame(1, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(0, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');

    // Saving again an already emptied field releases nothing more.
    $entity->save();
    $this->assertSame(1, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(0, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');

    // The suggestion can be used again afterwards.
    $entity->set('field_template_whisperer', 'foo')->save();
    $this->assertSame(2, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
    $this->assertSame(0, $usage->countUsage($storage->load('bar')), 'Usage of the "bar" suggestion.');
  }

  /**
   * Tests the usage is released when the suggestion ID is not its suggestion.
   */
  public function testUsageIsReleasedWhenTheIdDiffersFromTheSuggestion(): void {
    $storage = $this->container->get('entity_type.manager')->getStorage('template_whisperer_suggestion');
    $usage = $this->container->get('template_whisperer.suggestion.usage');

    // The field references the suggestion entity by its ID.
    $storage->create([
      'id'         => 'donation',
      'name'       => 'Donation',
      'suggestion' => 'donation_page',
    ])->save();

    // Emptying the field.
    $entity = $this->createEntityUsing('donation');
    $this->assertSame(1, $usage->countUsage($storage->load('donation')), 'Usage of the "donation" suggestion.');
    $entity->set('field_template_whisperer', NULL)->save();
    $this->assertSame(0, $usage->countUsage($storage->load('donation')), 'Usage of the "donation" suggestion.');

    // Switching to another suggestion.
    $entity->set('field_template_whisperer', 'donation')->save();
    $this->assertSame(1, $usage->countUsage($storage->load('donation')), 'Usage of the "donation" suggestion.');
    $entity->set('field_template_whisperer', 'foo')->save();
    $this->assertSame(0, $usage->countUsage($storage->load('donation')), 'Usage of the "donation" suggestion.');
    $this->assertSame(1, $usage->countUsage($storage->load('foo')), 'Usage of the "foo" suggestion.');
  }

}
