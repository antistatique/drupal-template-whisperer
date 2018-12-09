<?php

namespace Drupal\Tests\template_whisperer\Functional;

/**
 * @coversDefaultClass \Drupal\template_whisperer\Plugin\Field\FieldWidget\TemplateWhispererWidget
 *
 * Will assert the fields Template Whisperer will be placed in the advanced tabs
 * when possible. Otherwise will stay in place.
 * Eg. when used on taxonomy form or when embeed into an inline-edit-form.
 *
 * @group template_whisperer_functionnal_field
 * @group template_whisperer_functionnal
 * @group template_whisperer_ui
 * @group template_whisperer
 */
class WidgetFormElementTest extends TemplateWhispererTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'taxonomy',
    'node',
    'field_ui',
    'template_whisperer',
  ];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * The node (article) to tests with.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $article;

  /**
   * The taxonomy term (tags) to tests with.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $tag;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a user for tests.
    $admin_user = $this->drupalCreateUser([
      'access content',
      'administer taxonomy',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
      'bypass node access',
    ]);
    $this->drupalLogin($admin_user);

    $this->setupTag();
    $this->setupArticle();
  }

  /**
   * Setup default node for testing.
   */
  protected function setupArticle() {
    $em = $this->container->get('entity_type.manager');

    // Create an article content type that we will use for testing.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    // Add the Template Whispere field to the article content type.
    $storage = $em->getStorage('field_storage_config')->create([
      'field_name'  => 'field_template_whisperer_1',
      'entity_type' => 'node',
      'type'        => 'template_whisperer',
    ]);
    $storage->save();
    $em->getStorage('field_config')->create([
      'field_storage' => $storage,
      'bundle'        => 'article',
    ])->save();

    entity_get_form_display('node', 'article', 'default')
      ->setComponent('field_template_whisperer_1', [
        'type' => 'template_whisperer',
        'weight' => 20,
      ])->save();

    $this->article = $em->getStorage('node')->create([
      'type'  => 'article',
      'title' => 'Article N°1',
    ]);
    $this->article->save();
  }

  /**
   * Setup default taxonomy vocabulary with terms for testing.
   */
  protected function setupTag() {
    $em = $this->container->get('entity_type.manager');

    // Create a taxonomy vocabulary that we will use for testing.
    $em->getStorage('taxonomy_vocabulary')->create([
      'vid'  => 'tags',
      'name' => 'Tags',
    ])->save();

    // Add the Template Whispere field to the tags vocabulary.
    $storage = $em->getStorage('field_storage_config')->create([
      'field_name'  => 'field_template_whisperer_2',
      'entity_type' => 'taxonomy_term',
      'type'        => 'template_whisperer',
    ]);
    $storage->save();
    $em->getStorage('field_config')->create([
      'field_storage' => $storage,
      'bundle'        => 'tags',
    ])->save();

    entity_get_form_display('taxonomy_term', 'tags', 'default')
      ->setComponent('field_template_whisperer_2', [
        'type' => 'template_whisperer',
        'weight' => 20,
      ])->save();

    $this->tag = $em->getStorage('taxonomy_term')->create([
      'name' => 'Tags N°1',
      'vid'  => 'tags',
    ]);
    $this->tag->save();
  }

  /**
   * @covers ::formElement
   */
  public function testMoveFieldToAdvancedGroup() {
    // Access the node edit page.
    $this->drupalGet('node/' . $this->article->id() . '/edit');

    // Asserts the field is located on the Advanced Group - when possible.
    $this->assertSession()->elementExists('css', 'div[data-vertical-tabs-panes] #edit-field-template-whisperer-1-0 select');
  }

  /**
   * @covers ::formElement
   */
  public function testWhenNotPossibleStayInPlace() {
    // Access the taxonomy term edit page.
    $this->drupalGet('taxonomy/term/' . $this->tag->id() . '/edit');

    $this->assertSession()->elementNotExists('css', 'div[data-vertical-tabs-panes]');

    // Asserts the field is located at the end of the form.
    $this->assertSession()->elementExists('css', '#edit-field-template-whisperer-2-0');
  }

}
