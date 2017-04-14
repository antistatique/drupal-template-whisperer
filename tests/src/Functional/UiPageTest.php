<?php

namespace Drupal\Tests\template_whisperer\Functional;

/**
 * Tests event info pages and links.
 *
 * @group template_whisperer_ui
 */
class UiPageTest extends TemplateWhispererTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['template_whisperer'];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a user for tests.
    $account = $this->drupalCreateUser(['administer template whisperer suggestion entities']);
    $this->drupalLogin($account);
  }

  /**
   * Tests that the collection page works.
   */
  public function testCollectionPage() {
    $this->drupalGet('admin/structure/template-whisperer/list');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there is an empty listing.
    $this->assertSession()->pageTextContains('No suggestion has currently been set.');
  }

  /**
   * Tests that creating a template whisperer works.
   */
  public function testCreate() {
    $this->drupalGet('admin/structure/template-whisperer/list');
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

    $this->clickLink('Delete');
    $this->assertSession()->pageTextContains('Are you sure you want to delete the suggestion "Test Template Whisperer"?');
    $this->assertSession()->pageTextContains('This action cannot be undone.');

    $this->pressButton('Delete');
    $this->assertSession()->pageTextContains('No suggestion has currently been set.');
    $this->assertSession()->pageTextContains('The suggestion "Test Template Whisperer" has been deleted.');

    // Test Delete into entity.
    $this->testCreate();
    $this->clickLink('Edit');
    $this->clickLink('Delete');
    $this->assertSession()->pageTextContains('Are you sure you want to delete the suggestion "Test Template Whisperer"?');
    $this->assertSession()->pageTextContains('This action cannot be undone.');
    $this->clickLink('Cancel');
    $this->assertSession()->statusCodeEquals(200);
  }

}