<?php

namespace Drupal\Tests\template_whisperer\Functional;

use Drupal\block\BlockInterface;

/**
 * Ensures that Template Whisperer suggestions conditons' block work correctly.
 *
 * @group template_whisperer_functional_block
 * @group template_whisperer_functional
 * @group template_whisperer_ui
 * @group template_whisperer
 */
class ConditionalBlockTest extends TemplateWhispererTestBase {

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'taxonomy',
    'field_ui',
    'block',
    'template_whisperer',
  ];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * The block entities used by this test.
   *
   * @var \Drupal\block\BlockInterface[]
   */
  protected $blocks;

  /**
   * The articles Node used by this test.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $articles;

  /**
   * The tags Term used by this test.
   *
   * @var \Drupal\taxonomy\TermInterface[]
   */
  protected $tags;

  /**
   * The Template Whisperer suggestions used by this test.
   *
   * @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity[]
   */
  protected $suggestions;

  /**
   * An administrative user to configure the test environment.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    // Create and log in an administrative user.
    $this->adminUser = $this->drupalCreateUser([
      'administer blocks',
      'access administration pages',
    ]);

    // Setup default content.
    $this->setUpSuggestions();
    $this->setUpArticles();
    $this->setUpTags();
    $this->setUpBlock();
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Setup default blocks for testing.
   */
  protected function setUpBlock() {
    // Remove all blocks on the default theme.
    $block_storage = $this->entityTypeManager->getStorage('block');
    $blocks = $block_storage->loadByProperties(['theme' => $this->config('system.theme')->get('default')]);
    foreach ($blocks as $block) {
      $block->delete();
    }

    $this->blocks = [];
    $this->blocks[] = $this->drupalPlaceBlock('system_powered_by_block', [
      'label'         => $this->randomString(20),
      'region'        => 'footer',
      'label_display' => TRUE,
    ]);
  }

  /**
   * Update the block with template whisperer visibility conditions.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block to update.
   * @param array $suggestions
   *   Collection of suggestion ID to add as condition of block visibility.
   */
  protected function updateBlockSuggestionVisibility(BlockInterface $block, array $suggestions = []) {
    // Load the latests uncached block values.
    $block = $this->entityTypeManager->getStorage('block')->load($block->id());
    $visibility = [];
    foreach ($suggestions as $suggestion) {
      $visibility[$suggestion] = $suggestion;
    }
    $block->setVisibilityConfig('template_whisperer', [
      'suggestions'     => $visibility,
      'context_mapping' => [
        'node' => '@node.node_route_context:node',
      ],
    ]);
    $block->save();
  }

  /**
   * Setup default node for testing.
   */
  protected function setUpArticles() {
    // Create an article content type that we will use for testing.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    // Add the Template Whispere field to the article content type.
    $storage = $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->create([
        'field_name'  => 'field_template_whisperer',
        'entity_type' => 'node',
        'type'        => 'template_whisperer',
      ]);
    $storage->save();
    $this->entityTypeManager
      ->getStorage('field_config')
      ->create([
        'field_storage' => $storage,
        'bundle'        => 'article',
      ])->save();

    // Add default nodes.
    $this->articles = [];

    $article = $this->entityTypeManager->getStorage('node')->create([
      'type'  => 'article',
      'title' => 'Article N°1',
    ]);
    $article->save();
    $this->articles[] = $article;

    $article = $this->entityTypeManager->getStorage('node')->create([
      'type'  => 'article',
      'title' => 'Article N°2',
      'field_template_whisperer' => $this->suggestions[0]->id(),
    ]);
    $article->save();
    $this->articles[] = $article;

    $article = $this->entityTypeManager->getStorage('node')->create([
      'type'  => 'article',
      'title' => 'Article N°3',
      'field_template_whisperer' => $this->suggestions[1]->id(),
    ]);
    $article->save();
    $this->articles[] = $article;
  }

  /**
   * Setup default taxonomy vocabulary with terms for testing.
   */
  protected function setUpTags() {
    // Create a taxonomy vocabulary that we will use for testing.
    $this->entityTypeManager->getStorage('taxonomy_vocabulary')->create([
      'vid'  => 'tags',
      'name' => 'Tags',
    ])->save();

    // Add tests tags.
    $this->tags = [];
    $tag = $this->entityTypeManager->getStorage('taxonomy_term')->create([
      'name' => 'Tags N°1',
      'vid'  => 'tags',
    ]);
    $tag->save();
    $this->tags[] = $tag;

    $tag = $this->entityTypeManager->getStorage('taxonomy_term')->create([
      'name' => 'Tags N°2',
      'vid'  => 'tags',
    ]);
    $tag->save();
    $this->tags[] = $tag;
  }

  /**
   * Setup default template whisperer suggestions for testing.
   */
  protected function setUpSuggestions() {
    $this->suggestions = [];
    $suggestion = $this->entityTypeManager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'timeline',
      'name'       => 'Timeline',
      'suggestion' => 'timeline',
    ]);
    $suggestion->save();
    $this->suggestions[] = $suggestion;

    $suggestion = $this->entityTypeManager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'story',
      'name'       => 'Story',
      'suggestion' => 'story',
    ]);
    $suggestion->save();
    $this->suggestions[] = $suggestion;
  }

  /**
   * When nothing is configured, the block should be is visible on all entities.
   */
  public function testBlockVisibilityDefault() {
    // Update the placed block to be visible only on "Timeline".
    $this->updateBlockSuggestionVisibility($this->blocks[0], []);

    // Asserts by default the block is visible.
    // Block should displayed because no rules of block visibility are applied.
    $this->drupalGet($this->articles[0]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());
    $this->drupalGet($this->articles[1]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());
    $this->drupalGet($this->articles[2]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());

    // Asserts by default the block is visible.
    // Block should displayed because no rules of block visibility are applied.
    $this->drupalGet($this->tags[0]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());
    $this->drupalGet($this->tags[1]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());
  }

  /**
   * Asserts Condition Block configuration form works properly.
   */
  public function testBlockVisibilityConfigurationForm() {
    $this->drupalLogin($this->adminUser);

    // Update the placed block to be visible only on "Timeline".
    $this->drupalGet('admin/structure/block/manage/' . $this->blocks[0]->id());
    $edit['visibility[template_whisperer][suggestions][' . $this->suggestions[0]->id() . ']'] = TRUE;
    $this->drupalPostForm(NULL, $edit, 'Save block');

    // Asserts the configurations has been saved.
    $this->assertSession()->pageTextContains('The block configuration has been saved');
    $this->clickLink('Configure');
    $this->assertSession()->checkboxChecked('edit-visibility-template-whisperer-suggestions-' . $this->suggestions[0]->id());

    // Update the placed block to be visible only on "Timeline" & "Story".
    $this->drupalGet('admin/structure/block/manage/' . $this->blocks[0]->id());
    $edit['visibility[template_whisperer][suggestions][' . $this->suggestions[0]->id() . ']'] = TRUE;
    $edit['visibility[template_whisperer][suggestions][' . $this->suggestions[1]->id() . ']'] = TRUE;
    $this->drupalPostForm(NULL, $edit, 'Save block');
    // Asserts the configurations has been saved.
    $this->assertSession()->pageTextContains('The block configuration has been saved');
    $this->clickLink('Configure');
    $this->assertSession()->checkboxChecked('edit-visibility-template-whisperer-suggestions-' . $this->suggestions[0]->id());
    $this->assertSession()->checkboxChecked('edit-visibility-template-whisperer-suggestions-' . $this->suggestions[1]->id());
  }

  /**
   * Asserts the Condition Block will disable non-Node entities.
   */
  public function testBlockVisibilityNodeOnly() {
    // Update the placed block to be visible only on "Timeline".
    $this->updateBlockSuggestionVisibility($this->blocks[0], [
      $this->suggestions[0]->id(),
    ]);

    // Asserts the blocks is not visible on entity Taxonomy (others than Node)
    // once at least 1 Template Whisperer condition is applied.
    $this->drupalGet($this->tags[0]->toUrl());
    $this->assertSession()->pageTextNotContains($this->blocks[0]->label());
    $this->drupalGet($this->tags[1]->toUrl());
    $this->assertSession()->pageTextNotContains($this->blocks[0]->label());
  }

  /**
   * Asserts block visibility when configured to show only on "Timeline".
   */
  public function testBlockVisibility() {
    // Update the placed block to be visible only on "Timeline".
    $this->updateBlockSuggestionVisibility($this->blocks[0], [
      $this->suggestions[0]->id(),
    ]);

    // Asserts the blocks is visible according the single saved config.
    $this->drupalGet($this->articles[0]->toUrl());
    $this->assertSession()->pageTextNotContains($this->blocks[0]->label());
    $this->drupalGet($this->articles[1]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());
    $this->drupalGet($this->articles[2]->toUrl());
    $this->assertSession()->pageTextNotContains($this->blocks[0]->label());
  }

  /**
   * Asserts block visibility when configured to show on "Timeline" & "Story".
   */
  public function testBlockMultipleVisibility() {
    // Update the placed block to be visible only on "Timeline" & "Story".
    $this->updateBlockSuggestionVisibility($this->blocks[0], [
      $this->suggestions[0]->id(),
      $this->suggestions[1]->id(),
    ]);

    // Asserts the blocks is visible according the multiple saved config.
    $this->drupalGet($this->articles[0]->toUrl());
    $this->assertSession()->pageTextNotContains($this->blocks[0]->label());
    $this->drupalGet($this->articles[1]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());
    $this->drupalGet($this->articles[2]->toUrl());
    $this->assertSession()->pageTextContains($this->blocks[0]->label());
  }

}
