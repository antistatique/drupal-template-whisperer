<?php

namespace Drupal\Tests\template_whisperer\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Ensure the Template Whisperer help page works.
 *
 * Verifies that the module help page from hook_help() exists and can be
 * displayed.
 *
 * @group template_whisperer_functional_help
 * @group template_whisperer_functional
 * @group template_whisperer_ui
 * @group template_whisperer
 */
class HelpPageTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'help',
    'template_whisperer',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Since Drupal 10.2 accessing help page require a new permission.
    if (version_compare(\Drupal::VERSION, '10.2', '>=')) {
      $permissions = [
        'access administration pages',
        'access help pages',
      ];
    }
    else {
      $permissions = [
        'access administration pages',
      ];
    }

    // Create a user for tests.
    $admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests Template Whisperer help page.
   *
   * Verifies that the module help page from hook_help() exists and can be
   * displayed.
   */
  public function testHelp(): void {
    $this->drupalGet('/admin/help/template_whisperer');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('The module uses is own field and his own entity to generate more suggestions for your selected nodes.');
    $this->assertSession()->linkExists('suggestion');
  }

}
