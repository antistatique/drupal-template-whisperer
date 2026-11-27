<?php

namespace Drupal\Tests\template_whisperer\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\KernelTests\KernelTestBase;
use Drupal\template_whisperer\TemplateWhispererSuggestionUsage;
use Drupal\template_whisperer\TwigExtension\TwigExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the cacheability of the Template Whisperer suggestion usage.
 *
 * @group template_whisperer
 */
#[CoversClass(TemplateWhispererSuggestionUsage::class)]
#[CoversClass(TwigExtension::class)]
#[Group('template_whisperer_kernel')]
#[Group('template_whisperer')]
#[RunTestsInSeparateProcesses]
class SuggestionUsageCacheTest extends KernelTestBase {

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
   * The cache tags checksum service.
   *
   * @var \Drupal\Core\Cache\CacheTagsChecksumInterface
   */
  protected $checksum;

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
    $this->checksum = $this->container->get('cache_tags.invalidator.checksum');

    foreach (['foo', 'bar'] as $suggestion) {
      $this->storage('template_whisperer_suggestion')->create([
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
   * Gets the storage of an entity type.
   */
  protected function storage(string $entity_type_id): EntityStorageInterface {
    return $this->container->get('entity_type.manager')->getStorage($entity_type_id);
  }

  /**
   * Asserts which suggestion usage cache tags an operation invalidates.
   *
   * @param string[] $invalidated
   *   The suggestions whose usage cache tag must be invalidated.
   * @param string[] $untouched
   *   The suggestions whose usage cache tag must not be invalidated.
   * @param callable $operation
   *   The operation to run.
   */
  protected function assertUsageInvalidations(array $invalidated, array $untouched, callable $operation): void {
    $before = [];
    foreach ([...$invalidated, ...$untouched] as $suggestion) {
      $before[$suggestion] = $this->usageChecksum($suggestion);
    }

    $operation();

    foreach ($invalidated as $suggestion) {
      $this->assertNotSame($before[$suggestion], $this->usageChecksum($suggestion), "The usage cache tag of '$suggestion' is invalidated.");
    }
    foreach ($untouched as $suggestion) {
      $this->assertSame($before[$suggestion], $this->usageChecksum($suggestion), "The usage cache tag of '$suggestion' is not invalidated.");
    }
  }

  /**
   * Gets the current checksum of the usage cache tag of a suggestion.
   */
  protected function usageChecksum(string $suggestion): int|string {
    // Each read must hit the storage, not the checksum static cache.
    $this->checksum->reset();
    return $this->checksum->getCurrentChecksum(['template_whisperer_suggestion_usage:' . $suggestion]);
  }

  /**
   * Tests the usage cache tag is invalidated when the usage changes.
   */
  public function testUsageChangesInvalidateTheSuggestionCacheTag(): void {
    $entity = $this->storage('entity_test')->create([]);

    // A new entity using a suggestion.
    $this->assertUsageInvalidations(['foo'], ['bar'], function () use ($entity) {
      $entity->set('field_template_whisperer', 'foo')->save();
    });

    // Saving without changing the suggestion.
    $this->assertUsageInvalidations([], ['foo', 'bar'], function () use ($entity) {
      $entity->set('name', 'renamed')->save();
    });

    // Switching from a suggestion to another one.
    $this->assertUsageInvalidations(['foo', 'bar'], [], function () use ($entity) {
      $entity->set('field_template_whisperer', 'bar')->save();
    });

    // Emptying the field.
    $this->assertUsageInvalidations(['bar'], ['foo'], function () use ($entity) {
      $entity->set('field_template_whisperer', NULL)->save();
    });

    // Deleting an entity using a suggestion.
    $entity->set('field_template_whisperer', 'foo')->save();
    $this->assertUsageInvalidations(['foo'], ['bar'], function () use ($entity) {
      $entity->delete();
    });
  }

  /**
   * Tests the usage cache tag follows the suggestion of a suggestion entity.
   */
  public function testSuggestionEntityChangesInvalidateTheSuggestionCacheTag(): void {
    $this->assertUsageInvalidations(['baz'], ['foo', 'bar'], function () {
      $this->storage('template_whisperer_suggestion')->load('foo')->setSuggestion('baz')->save();
    });
  }

  /**
   * Tests the cacheability exposed to the callers of listUsage().
   */
  public function testGetUsageCacheability(): void {
    $entity = $this->storage('entity_test')->create(['field_template_whisperer' => 'foo']);
    $entity->save();

    /** @var \Drupal\template_whisperer\TemplateWhispererSuggestionUsage $usage */
    $usage = $this->container->get('template_whisperer.suggestion.usage');
    $this->assertSame(['template_whisperer_suggestion_usage:foo'], $usage->getCacheTags('foo'));

    $tags = $usage->getUsageCacheability($this->storage('template_whisperer_suggestion')->load('foo'))->getCacheTags();
    sort($tags);
    $this->assertSame([
      'config:template_whisperer.template_whisperer_suggestion.foo',
      'entity_test:' . $entity->id(),
      'template_whisperer_suggestion_usage:foo',
    ], $tags);
  }

  /**
   * Tests tw_suggestion_entities() bubbles the cacheability of the usage.
   */
  public function testTwigFunctionBubblesTheUsageCacheability(): void {
    $entity = $this->storage('entity_test')->create(['field_template_whisperer' => 'foo']);
    $entity->save();

    $this->assertEqualsCanonicalizing([
      'config:template_whisperer.template_whisperer_suggestion.foo',
      'entity_test:' . $entity->id(),
      'template_whisperer_suggestion_usage:foo',
    ], $this->bubbledCacheTags('foo'));

    // A suggestion nothing declares yet is still tracked.
    $this->assertSame(['template_whisperer_suggestion_usage:unknown'], $this->bubbledCacheTags('unknown'));
  }

  /**
   * Gets the cache tags bubbled by tw_suggestion_entities().
   */
  protected function bubbledCacheTags(string $suggestion): array {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');
    $context = new RenderContext();
    $renderer->executeInRenderContext($context, function () use ($suggestion) {
      $this->container->get('template_whisperer.twig.extension')->getEntitiesFromSuggestion($suggestion);
    });

    return $context->isEmpty() ? [] : CacheableMetadata::createFromObject($context->pop())->getCacheTags();
  }

  /**
   * Tests a cached render changes once a new entity uses the suggestion.
   */
  public function testCachedRenderIsInvalidatedWhenTheUsageChanges(): void {
    // The first usage is rendered and cached, as the header of a site would.
    $this->storage('entity_test')->create(['field_template_whisperer' => 'foo'])->save();
    $this->assertSame('1|0', $this->renderCached());

    // A new entity starts using a suggestion used by nothing when cached: no
    // entity cache tag was there to bubble.
    $entity = $this->storage('entity_test')->create(['field_template_whisperer' => 'bar']);
    $entity->save();
    $this->assertSame('1|1', $this->renderCached());

    // An entity starts using an already used suggestion.
    $entity->set('field_template_whisperer', 'foo')->save();
    $this->assertSame('2|0', $this->renderCached());

    // An entity stops using the suggestion once its field is emptied.
    $entity->set('field_template_whisperer', NULL)->save();
    $this->assertSame('1|0', $this->renderCached());

    // An entity using the suggestion is deleted.
    $entity->set('field_template_whisperer', 'foo')->save();
    $this->assertSame('2|0', $this->renderCached());
    $entity->delete();
    $this->assertSame('1|0', $this->renderCached());
  }

  /**
   * Renders a render cached template listing the usages of the suggestions.
   */
  protected function renderCached(): string {
    $build = [
      '#type'     => 'inline_template',
      '#template' => "{{ tw_suggestion_entities('foo')|length }}|{{ tw_suggestion_entities('bar')|length }}",
      '#cache'    => ['keys' => ['template_whisperer_test']],
    ];

    return (string) $this->container->get('renderer')->renderRoot($build);
  }

}
