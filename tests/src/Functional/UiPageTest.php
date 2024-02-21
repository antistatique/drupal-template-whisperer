<?php

namespace Drupal\Tests\template_whisperer\Functional;

/**
 * Tests event info pages and links.
 *
 * @group template_whisperer_functional_page
 * @group template_whisperer_functional
 * @group template_whisperer_ui
 * @group template_whisperer
 */
class UiPageTest extends TemplateWhispererTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['template_whisperer'];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * The Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    /** @var \Drupal\Core\Entity\EntityTypeManager $entityTypeManager */
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    // Create an page content type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic Page']);

    // Set up our Template Whisperer field on the Basic Page content type.
    $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->create([
        'field_name'  => 'field_template_whisperer',
        'entity_type' => 'node',
        'type'        => 'template_whisperer',
      ])->save();
    $this->entityTypeManager
      ->getStorage('field_config')
      ->create([
        'entity_type' => 'node',
        'field_name'  => 'field_template_whisperer',
        'bundle'      => 'page',
      ])->save();

    // Create a user for tests & logged in.
    $account = $this->drupalCreateUser(['administer template whisperer suggestion entities']);
    $this->drupalLogin($account);
  }

  /**
   * Tests that the collection page works.
   */
  public function testCollectionPage() {
    $this->drupalGet('admin/structure/template-whisperer');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there is an empty listing.
    $this->assertSession()->pageTextContains('No suggestion has currently been set.');
  }

  /**
   * Tests that creating a template whisperer works.
   */
  public function testCreate() {
    $this->drupalGet('admin/structure/template-whisperer');

    $this->clickLink('Add suggestion');

    $this->fillField('Name', 'Test Template Whisperer');
    $this->fillField('Suggestion', 'test');
    $this->pressButton('Save');

    // Must be redirected on the collection page.
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Created the "Test Template Whisperer" suggestion.');

    // Edit the created template whisperer.
    $this->clickLink('Edit');
    $this->assertSession()->statusCodeEquals(200);
    $this->pressButton('Save');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Saved the "Test Template Whisperer" suggestion.');
  }

  /**
   * Tests that deleting works.
   */
  public function testDelete() {
    // Setup a template whisperer with one suggestion.
    $this->testCreate();

    // Test Delete from listing page.
    $this->clickLink('Delete');
    $this->assertSession()->addressEquals('admin/structure/template-whisperer/test/delete');
    $this->assertSession()->pageTextContains('Are you sure you want to delete the suggestion "Test Template Whisperer"?');
    $this->assertSession()->pageTextContains('This action cannot be undone.');
    $this->pressButton('Delete');
    $this->assertSession()->addressEquals('admin/structure/template-whisperer');
    $this->assertSession()->pageTextContains('No suggestion has currently been set.');
    $this->assertSession()->pageTextContains('The suggestion "Test Template Whisperer" has been deleted.');

    // Test Delete into entity.
    $this->testCreate();
    $this->clickLink('Edit');
    $this->assertSession()->addressEquals('admin/structure/template-whisperer/test/edit');
    $this->clickLink('Delete');
    $this->assertSession()->addressEquals('admin/structure/template-whisperer/test/delete');
    $this->assertSession()->pageTextContains('Are you sure you want to delete the suggestion "Test Template Whisperer"?');
    $this->assertSession()->pageTextContains('This action cannot be undone.');
    $this->pressButton('Delete');
    $this->assertSession()->addressEquals('admin/structure/template-whisperer');
    $this->assertSession()->pageTextContains('No suggestion has currently been set.');
    $this->assertSession()->pageTextContains('The suggestion "Test Template Whisperer" has been deleted.');

    // Cancel deletion.
    $this->testCreate();
    $this->clickLink('Delete');
    $this->assertSession()->addressEquals('admin/structure/template-whisperer/test/delete');
    $this->clickLink('Cancel');
    $this->assertSession()->pageTextNotContains('The suggestion "Test Template Whisperer" has been deleted.');
  }

  /**
   * Tests the canonical page of a suggestion entity.
   *
   * Verifies the canonical page defined as handlers:view_builder on annotation.
   *
   * @ConfigEntityType on \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity.
   * works.
   */
  public function testCanonicalPage() {
    $suggestion = $this->entityTypeManager->getStorage('template_whisperer_suggestion')
      ->create([
        'id'         => 'test',
        'name'       => 'Test Template Whisperer',
        'suggestion' => 'test',
      ]);
    $suggestion->save();

    $this->drupalGet('admin/structure/template-whisperer/test');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests that the usage page works.
   */
  public function testUsagePage() {
    $suggestion = $this->entityTypeManager->getStorage('template_whisperer_suggestion')
      ->create([
        'id'         => 'test',
        'name'       => 'Test Template Whisperer',
        'suggestion' => 'test',
      ]);
    $suggestion->save();

    // Ensure when not used the usage page still works.
    $this->drupalGet('admin/structure/template-whisperer/test/usage');
    $this->assertSession()->statusCodeEquals(200);

    // Create a Basic Page content type that we will use for testing.
    $page = $this->entityTypeManager->getStorage('node')->create([
      'type'  => 'page',
      'title' => 'Basic Page',
    ]);
    $page->save();

    $this->drupalGet('admin/structure/template-whisperer/test/usage');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there is an empty listing.
    $this->assertSession()->pageTextContains('This suggestion has not been currently used.');

    // Create a page & attache a suggestion again.
    $page = $this->entityTypeManager->getStorage('node')->create([
      'type'  => 'page',
      'title' => 'Basic Page N°2',
      'field_template_whisperer' => 'test',
    ]);
    $page->save();

    // Asserts that attaching a suggestion to a Template whisperer field int0
    // an entity (here a basic page), it will increment the usage count.
    $this->drupalGet('admin/structure/template-whisperer/test/usage');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementContains('css', 'table', 'Basic Page N°2');
    $this->assertSession()->elementContains('css', 'table', 'node');
    $this->assertSession()->elementContains('css', 'table', 'template_whisperer');
    $this->assertSession()->elementContains('css', 'table', '1');
  }

  /**
   * Tests that deleting when already used show a warning message to the user.
   */
  public function testDeleteWhenUse() {
    // Create a default Template Whisperer.
    // Set up our entity_type and user type for the field.
    $this->entityTypeManager->getStorage('template_whisperer_suggestion')->create([
      'id'         => 'test',
      'name'       => 'Test Template Whisperer',
      'suggestion' => 'test',
    ])->save();

    // Create a default page & attache a suggestion.
    $node = $this->entityTypeManager->getStorage('node')->create([
      'type'  => 'page',
      'title' => 'Basic Page',
      'field_template_whisperer' => 'test',
    ]);
    $node->save();

    $this->drupalGet('admin/structure/template-whisperer/test/delete');
    $this->assertSession()->pageTextContains('The suggestion "Test Template Whisperer" is used in 1 place. Any usage of this suggestion will be lost. This action cannot be undone.');

    // Create a second page & attache a suggestion again.
    $node = $this->entityTypeManager->getStorage('node')->create([
      'type'  => 'page',
      'title' => 'Basic Page N°2',
      'field_template_whisperer' => 'test',
    ]);
    $node->save();

    $this->drupalGet('admin/structure/template-whisperer/test/delete');
    $this->assertSession()->pageTextContains('The suggestion "Test Template Whisperer" is used in 2 places. Any usage of this suggestion will be lost. This action cannot be undone.');
  }

}
