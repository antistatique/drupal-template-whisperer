<?php

namespace Drupal\Tests\template_whisperer\Functional;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

/**
 * Ensures that Template Whisperer suggestions conditons' block work correctly.
 *
 * @group template_whisperer_ui
 */
class UiConditionalBlockTest extends TemplateWhispererTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['field_ui', 'block', 'template_whisperer'];

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
    // Create and log in an administrative user.
    $this->adminUser = $this->drupalCreateUser([
      'administer blocks',
      'access administration pages',
    ]);
    $this->drupalLogin($this->adminUser);

    // Enable some test blocks.
    $blocks_values = [
      [
        'label' => 'Powered by Drupal',
        'tr' => '16',
        'plugin_id' => 'system_powered_by_block',
        'settings' => ['region' => 'footer', 'id' => 'powered'],
        'test_weight' => '0',
      ],
    ];
    $this->blocks = [];
    foreach ($blocks_values as $values) {
      $this->blocks[] = $this->drupalPlaceBlock($values['plugin_id'], $values['settings']);
    }

    $block_storage = $this->container->get('entity_type.manager')->getStorage('block');
    $blocks = $block_storage->loadByProperties(['theme' => $this->config('system.theme')->get('default')]);
    foreach ($blocks as $block) {
      $block->delete();
    }

    // Enable some test suggestions.
    $suggestions_values = [
      [
        'id'         => 'timeline',
        'name'       => 'Timeline',
        'suggestion' => 'timeline',
      ],
      [
        'id'         => 'story',
        'name'       => 'Story',
        'suggestion' => 'story',
      ],
    ];
    $this->suggestions = [];
    foreach ($suggestions_values as $values) {
      $suggestion = $this->container->get('entity_type.manager')
        ->getStorage('template_whisperer_suggestion')
        ->create($values);
      $suggestion->save();
      $this->suggestions[] = $suggestion;
    }

    // Create an article content type that we will use for testing.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    // Add the Template Whispere field to the article content type.
    $field_storage = FieldStorageConfig::create([
      'field_name'  => 'field_template_whisperer',
      'entity_type' => 'node',
      'type'        => 'template_whisperer',
    ]);
    $field_storage->save();
    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle'        => 'article',
      'label'         => $this->randomMachineName(),
    ]);
    $instance->save();

    // Enable some test articles.
    $articles_values = [
      [
        'type'  => 'article',
        'title' => 'Article N°1',
      ],
      [
        'type'  => 'article',
        'title' => 'Article N°2',
        'field_template_whisperer' => $this->suggestions[0]->id(),
      ],
      [
        'type'  => 'article',
        'title' => 'Article N°3',
        'field_template_whisperer' => $this->suggestions[1]->id(),
      ],
    ];
    $this->articles = [];
    foreach ($articles_values as $values) {
      $article = $this->container->get('entity_type.manager')
        ->getStorage('node')
        ->create($values);
      $article->save();
      $this->articles[] = $article;
    }

    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Tests block visibility according Template Whisperer suggestions condition.
   */
  public function testBlockVisibility() {
    $block_name = 'system_powered_by_block';
    // Create a random title for the block.
    $title = $this->randomMachineName(8);
    // Enable a standard block.
    $default_theme = $this->config('system.theme')->get('default');
    $edit = [
      'id'                      => strtolower($this->randomMachineName(8)),
      'region'                  => 'sidebar_first',
      'settings[label]'         => $title,
      'settings[label_display]' => TRUE,
    ];

    // Set the block to be hidden on Template Whisperer Suggestion "Timeline".
    $edit['visibility[template_whisperer][suggestions][' . $this->suggestions[0]->id() . ']'] = TRUE;
    $this->drupalGet('admin/structure/block/add/' . $block_name . '/' . $default_theme);

    $this->drupalPostForm(NULL, $edit, t('Save block'));
    $this->assertText('The block configuration has been saved.', 'Block was saved');

    $this->clickLink('Configure');
    $this->assertFieldChecked('edit-visibility-template-whisperer-suggestions-' . $this->suggestions[0]->id());

    $this->drupalGet($this->articles[0]->toUrl());
    $this->assertNoText($title, 'Block was not displayed according to block visibility rules.');

    $this->drupalGet($this->articles[1]->toUrl());
    $this->assertText($title, 'Block was displayed according to block visibility rules.');

    $this->drupalGet($this->articles[2]->toUrl());
    $this->assertNoText($title, 'Block was displayed according to block visibility rules.');
  }

  /**
   * Tests block visibility according Template Whisperer suggestions condition.
   */
  public function testBlockMultipleVisibility() {
    $block_name = 'system_powered_by_block';
    // Create a random title for the block.
    $title = $this->randomMachineName(8);
    // Enable a standard block.
    $default_theme = $this->config('system.theme')->get('default');
    $edit = [
      'id'                      => strtolower($this->randomMachineName(8)),
      'region'                  => 'sidebar_first',
      'settings[label]'         => $title,
      'settings[label_display]' => TRUE,
    ];

    // Set the block to be hidden on Template Whisperer Suggestion "Timeline".
    $edit['visibility[template_whisperer][suggestions][' . $this->suggestions[0]->id() . ']'] = TRUE;
    $edit['visibility[template_whisperer][suggestions][' . $this->suggestions[1]->id() . ']'] = TRUE;
    $this->drupalGet('admin/structure/block/add/' . $block_name . '/' . $default_theme);

    $this->drupalPostForm(NULL, $edit, t('Save block'));
    $this->assertText('The block configuration has been saved.', 'Block was saved');

    $this->clickLink('Configure');
    $this->assertFieldChecked('edit-visibility-template-whisperer-suggestions-' . $this->suggestions[0]->id());
    $this->assertFieldChecked('edit-visibility-template-whisperer-suggestions-' . $this->suggestions[1]->id());

    $this->drupalGet($this->articles[0]->toUrl());
    $this->assertNoText($title, 'Block was not displayed according to block visibility rules.');

    $this->drupalGet($this->articles[1]->toUrl());
    $this->assertText($title, 'Block was displayed according to block visibility rules.');

    $this->drupalGet($this->articles[2]->toUrl());
    $this->assertText($title, 'Block was displayed according to block visibility rules.');
  }

}
