<?php

namespace Drupal\Tests\template_whisperer\Functional;

/**
 * Tests event info pages and links.
 *
 * @group template_whisperer_ui
 */
class UiFieldTest extends TemplateWhispererTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'field_ui', 'template_whisperer'];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * The article Node for the test.
   *
   * @var \Drupal\node\Node
   */
  protected $article;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a user for tests.
    $admin_user = $this->drupalCreateUser([
      'access content',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'administer node display',
      'bypass node access',
    ]);
    $this->drupalLogin($admin_user);

    $this->template = $this->container->get('entity.manager')->getStorage('template_whisperer_suggestion')
      ->create([
        'id'         => 'googlemap',
        'name'       => 'Article - GoogleMap',
        'suggestion' => 'googlemap',
      ]);
    $this->template->save();

    // Create an article content type that we will use for testing.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));

    $this->article = $this->container->get('entity_type.manager')->getStorage('node')
      ->create([
        'type'  => 'article',
        'title' => 'Article',
      ]);
    $this->article->save();
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    $this->debugOff();
  }

  /**
   * Tests that the Template Whisperer field works.
   */
  public function testAddField() {
    // Access the Field management of Article.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $this->assertSession()->statusCodeEquals(200);

    // Add the Template Whisperer field.
    $this->clickLink('Add field');
    $this->fillField('Add a new field', 'template_whisperer');
    $this->fillField('Label', 'Template Whisperer');
    $this->fillField('Machine-readable name', 'template_whisperer');
    $this->pressButton('Save and continue');
    $this->assertSession()->statusCodeEquals(200);

    // Check the cardinality.
    $this->assertSession()->pageTextContains('These settings apply to the Template Whisperer field everywhere it is used. These settings impact the way that data is stored in the database and cannot be changed once data has been created.');
    $this->pressButton('Save field settings');
    $this->assertSession()->statusCodeEquals(200);

    // Finalize the field.
    $this->assertSession()->pageTextContains('Updated field Template Whisperer field settings.');
    $this->pressButton('Save settings');
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextContains('Saved Template Whisperer configuration.');
  }

  /**
   * Tests that the Template Whisperer field added is displayed.
   */
  public function testFieldExist() {
    $this->testAddField();

    // Access the node edit page.
    $this->drupalGet('node/' . $this->article->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);

    // Check our custom field exist.
    $this->assertSession()->elementContains('css', '#edit-field-template-whisperer-0', 'Select a template');
  }

  /**
   * Tests that the Template Whisperer saved is used as suggestion of the node.
   */
  public function testFieldSaved() {
    $this->testFieldExist();

    // Save the node with our custom field.
    $this->fillField('Select a template', $this->template->id());
    $this->pressButton('Save');

    $this->debugOn();

    // Access the node canonical page.
    $this->drupalGet('node/' . $this->article->id());
    $this->assertSession()->statusCodeEquals(200);

    $output = $this->getSession()->getPage();

    $this->assertTrue(strpos($output->getContent(), '<!-- THEME HOOK: \'node\' -->') !== FALSE, 'node theme hook debug comment is present.');

    $this->assertTrue(strpos($output->getContent(), '* node--article--googlemap.html.twig') !== FALSE, 'node--article--googlemap theme hook debug comment is present.');
  }

  /**
   * Tests the Node whitout Template saved, don't suggestion it.
   */
  public function testFieldWhitoutTemplate() {
    $article = $this->container->get('entity_type.manager')->getStorage('node')
      ->create([
        'type'  => 'article',
        'title' => 'Article N°2',
      ]);
    $article->save();
    $this->testFieldSaved();

    $this->debugOn();

    // Access the node canonical page.
    $this->drupalGet('node/' . $article->id());
    $this->assertSession()->statusCodeEquals(200);

    $output = $this->getSession()->getPage();

    $this->assertTrue(strpos($output->getContent(), '<!-- THEME HOOK: \'node\' -->') !== FALSE, 'node theme hook debug comment is present.');

    $this->assertTrue(strpos($output->getContent(), '* node--article--googlemap.html.twig') === FALSE, 'node--article--googlemap theme hook debug comment is NOT present.');
  }

}
