<?php

namespace Drupal\Tests\template_whisperer\Functional;

use Drupal\Tests\BrowserTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Ensure the Template Whisperer help page works.
 *
 * Verifies that the module help page from hook_help() exists and can be
 * displayed.
 *
 * @group template_whisperer_functional
 * @group template_whisperer
 */
#[Group('template_whisperer_functional_help')]
#[Group('template_whisperer_functional')]
#[Group('template_whisperer_ui')]
#[Group('template_whisperer')]
#[RunTestsInSeparateProcesses]
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

    $permissions = [
      'access administration pages',
      'access help pages',
    ];

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
