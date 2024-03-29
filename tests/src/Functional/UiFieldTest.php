<?php

namespace Drupal\Tests\template_whisperer\Functional;

/**
 * Tests event info pages and links.
 *
 * @group template_whisperer_functional_field
 * @group template_whisperer_functional
 * @group template_whisperer_ui
 * @group template_whisperer
 */
class UiFieldTest extends TemplateWhispererTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'field_ui', 'template_whisperer'];

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
   * The Template Whisperer suggestion used for the test.
   *
   * @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity
   */
  protected $template;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
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

    $this->template = $this->container->get('entity_type.manager')->getStorage('template_whisperer_suggestion')
      ->create([
        'id'         => 'googlemap',
        'name'       => 'Article - GoogleMap',
        'suggestion' => 'googlemap',
      ]);
    $this->template->save();

    // Create an article content type that we will use for testing.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

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
  protected function tearDown(): void {
    $this->debugOff();
    parent::tearDown();
  }

  /**
   * Tests that the Template Whisperer field works.
   */
  public function testAddField() {
    // Access the Field management of Article.
    $this->drupalGet('admin/structure/types/manage/article/fields');
    $this->assertSession()->statusCodeEquals(200);

    // Add the Template Whisperer field.
    // Since Drupal 10.1 the button "add field" text has been changed.
    if (version_compare(\Drupal::VERSION, '10.1', '>=')) {
      $this->clickLink('Create a new field');
    }
    else {
      $this->clickLink('Add field');
    }
    $this->assertSession()->addressEquals('admin/structure/types/manage/article/fields/add-field');

    // Add the Template Whisperer field.
    // Since Drupal 10.2 the field type has been changed from select to radio.
    if (version_compare(\Drupal::VERSION, '10.2', '>=')) {
      $this->assertSession()->elementExists('css', "[name='new_storage_type'][value='template_whisperer']");
      $this->getSession()->getPage()->selectFieldOption('new_storage_type', 'template_whisperer');
    }
    else {
      $this->fillField('Add a new field', 'template_whisperer');
    }

    // Since Drupal 11.0 The field label and machine_name are on another page.
    if (version_compare(\Drupal::VERSION, '11', '>=')) {
      $this->pressButton('Continue');
      $this->assertSession()->addressEquals('admin/structure/types/manage/article/fields/add-field');
    }

    $this->fillField('label', 'Template Whisperer');
    $this->fillField('Machine-readable name', 'template_whisperer');

    // Since Drupal 10.2 the submit button text changed.
    if (version_compare(\Drupal::VERSION, '10.2', '>=')) {
      $this->pressButton('Continue');
    }
    else {
      $this->pressButton('Save and continue');
    }
    $this->assertSession()->statusCodeEquals(200);

    // Check the cardinality.
    $this->assertSession()->pageTextContains("These settings apply to the Template Whisperer field everywhere it is used.");

    // Since Drupal 10.2 the storage page has been removed.
    if (version_compare(\Drupal::VERSION, '10.2', '>=')) {
      $this->pressButton('Save settings');
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains('Saved Template Whisperer configuration.');
    }
    else {
      $this->pressButton('Save field settings');
      $this->assertSession()->statusCodeEquals(200);
      // Finalize the field configuration.
      $this->assertSession()->pageTextContains('Updated field Template Whisperer field settings.');
      $this->pressButton('Save settings');
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains('Saved Template Whisperer configuration.');
    }
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
   * Test the Fields Settings.
   *
   * Tests that a previously added Template Whisperer field propose
   * Available Suggestions in the settings section & store this configuration.
   */
  public function testFieldSettings() {
    $this->testAddField();

    // Access the field settings page.
    $this->drupalGet('/admin/structure/types/manage/article/fields/node.article.field_template_whisperer');
    $this->assertSession()->statusCodeEquals(200);

    $session = $this->getSession();
    $page = $session->getPage();

    // Check Settings field.
    $this->assertSession()->elementContains('css', '#edit-settings-handler', 'These settings apply only to the Template Whisperer field when used in the Article type.');
    $this->assertSession()->elementContains('css', '#edit-settings-handler', 'Reference type');
    $this->assertSession()->elementContains('css', '#edit-settings-handler', 'Available Suggestions');
    $this->assertSession()->elementContains('css', '#edit-settings-handler-suggestions', 'Article - GoogleMap');
    $this->assertSession()->checkboxNotChecked('settings[handler][suggestions][googlemap]');
    $page->checkField('settings[handler][suggestions][googlemap]');
    $this->pressButton('Save settings');

    // Return to the field settings page.
    $this->drupalGet('/admin/structure/types/manage/article/fields/node.article.field_template_whisperer');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->checkboxChecked('settings[handler][suggestions][googlemap]');
  }

  /**
   * Test the Fields Settings.
   *
   * Tests that a previously non-setted Template Whisperer field propose
   * all Available Suggestions in the widget.
   */
  public function testFieldSettingsInWidgetNoSelection() {
    $template_homepage = $this->container->get('entity_type.manager')->getStorage('template_whisperer_suggestion')
      ->create([
        'id'         => 'homepage',
        'name'       => 'Article - Homepage',
        'suggestion' => 'homepage',
      ]);
    $template_homepage->save();

    $this->testAddField();

    // Access the node edit page.
    $this->drupalGet('node/' . $this->article->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->elementContains('css', '#edit-field-template-whisperer-0-target-id', 'Article - GoogleMap');
    $this->assertSession()->elementContains('css', '#edit-field-template-whisperer-0-target-id', 'Article - Homepage');
  }

  /**
   * Test the Fields Settings.
   *
   * Tests that a previously setted Template Whisperer field propose
   * only selected Available Suggestions in the widget.
   */
  public function testFieldSettingsInWidgetWithSelection() {
    $template_homepage = $this->container->get('entity_type.manager')->getStorage('template_whisperer_suggestion')
      ->create([
        'id'         => 'homepage',
        'name'       => 'Article - Homepage',
        'suggestion' => 'homepage',
      ]);
    $template_homepage->save();

    $this->testFieldSettings();

    // Return to the field settings page.
    $this->drupalGet('/admin/structure/types/manage/article/fields/node.article.field_template_whisperer');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->checkboxChecked('settings[handler][suggestions][googlemap]');
    $this->assertSession()->checkboxNotChecked('settings[handler][suggestions][homepage]');

    // Access the node edit page.
    $this->drupalGet('node/' . $this->article->id() . '/edit');
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->elementContains('css', '#edit-field-template-whisperer-0-target-id', 'Article - GoogleMap');
    $this->assertSession()->elementNotContains('css', '#edit-field-template-whisperer-0-target-id', 'Article - Homepage');
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

    // Asserts the debug mode of twig is enabled.
    $this->assertTrue(strpos($output->getContent(), '<!-- THEME HOOK: \'node\' -->') !== FALSE);

    // Asserts that all Page Template Whisperer based suggestions are present.
    $this->assertTrue(strpos($output->getContent(), '* page--node--1--googlemap.html.twig') !== FALSE);
    $this->assertTrue(strpos($output->getContent(), '* page--node--googlemap.html.twig') !== FALSE);

    // Asserts that all Entity Template Whisperer based suggestions are present.
    $this->assertTrue(strpos($output->getContent(), '* node--article--googlemap.html.twig') !== FALSE);
    $this->assertTrue(strpos($output->getContent(), '* node--1--article--googlemap.html.twig') !== FALSE);
    $this->assertTrue(strpos($output->getContent(), '* node--article--full--googlemap.html.twig') !== FALSE);
    $this->assertTrue(strpos($output->getContent(), '* node--1--article--full--googlemap.html.twig') !== FALSE);
  }

  /**
   * Tests the Node whiteout Template saved, don't suggestion it.
   */
  public function testFieldWithoutTemplate() {
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

    // Asserts the debug mode of twig is enabled.
    $this->assertTrue(strpos($output->getContent(), '<!-- THEME HOOK: \'node\' -->') !== FALSE);

    // Asserts that Page Template Whisperer based suggestions are not present.
    $this->assertTrue(strpos($output->getContent(), '* page--node--1--googlemap.html.twig') === FALSE);
    $this->assertTrue(strpos($output->getContent(), '* page--node--googlemap.html.twig') === FALSE);

    // Asserts that Entity Template Whisperer based suggestions are not present.
    $this->assertTrue(strpos($output->getContent(), '* node--article--googlemap.html.twig') === FALSE);
    $this->assertTrue(strpos($output->getContent(), '* node--1--article--googlemap.html.twig') === FALSE);
    $this->assertTrue(strpos($output->getContent(), '* node--article--full--googlemap.html.twig') === FALSE);
    $this->assertTrue(strpos($output->getContent(), '* node--1--article--full--googlemap.html.twig') === FALSE);
  }

}
