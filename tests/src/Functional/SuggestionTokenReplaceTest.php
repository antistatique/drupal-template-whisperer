<?php

namespace Drupal\Tests\template_whisperer\Functional;

/**
 * Check suggestion tokens replacement.
 *
 * @covers ::template_whisperer_token_info
 * @covers ::template_whisperer_tokens
 *
 * @group template_whisperer_functionnal_token
 * @group template_whisperer_functionnal
 * @group template_whisperer
 */
class SuggestionTokenReplaceTest extends TemplateWhispererTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'template_whisperer'];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * Collection of Template Whisperer test entites.
   *
   * @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity[]
   */
  private $suggestions;

  /**
   * The node with an attached suggestion for this tests.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $testNode;

  /**
   * {@inheritdoc}
   *
   * This sets up the node and user types to use the field plugins.
   */
  protected function setUp() {
    parent::setUp();

    $type_manager = $this->container->get('entity_type.manager');

    // Create an article content type.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    $suggestion_storage = $type_manager->getStorage('template_whisperer_suggestion');

    // Create a default Template Whisperer.
    // Set up our entity_type and user type for the field.
    $this->suggestions['foo'] = $suggestion_storage->create([
      'id'         => 'foo',
      'name'       => 'Foo',
      'suggestion' => 'foo',
    ]);
    $this->suggestions['foo']->save();
    $this->suggestions['bar'] = $suggestion_storage->create([
      'id'         => 'bar',
      'name'       => 'Bar',
      'suggestion' => 'bar',
    ]);
    $this->suggestions['bar']->save();

    // Set up our entity_type and user type for the field.
    $type_manager
      ->getStorage('field_storage_config')
      ->create([
        'field_name'  => 'field_template_whisperer',
        'entity_type' => 'node',
        'type'        => 'template_whisperer',
      ])->save();

    $type_manager
      ->getStorage('field_config')
      ->create([
        'entity_type' => 'node',
        'field_name'  => 'field_template_whisperer',
        'bundle'      => 'article',
      ])->save();

    // Create a default entity & attache a suggestion.
    $this->testNode = $this->drupalCreateNode(['type' => 'article']);

    $target_id = $this->suggestions['foo']->getSuggestion();
    $this->testNode->field_template_whisperer->target_id = $target_id;
    $this->testNode->save();

    // Create the usage entry.
    $twSuggestionUsage = $this->container->get('template_whisperer.suggestion.usage');
    $twSuggestionUsage->add($this->suggestions['foo'], 'template_whisperer', $this->testNode->getEntityTypeId(), $this->testNode->id());
  }

  /**
   * Test the token works with valid parameters.
   */
  public function testSuggestionTokenReplacement() {
    $token_service = \Drupal::token();
    $language_interface = \Drupal::languageManager()->getCurrentLanguage();

    // Tokens options.
    $options = ['langcode' => $language_interface->getId()];

    // Suggestion tokens.
    $replacement = $token_service->replace('[suggestion:name]', ['suggestion' => $this->suggestions['foo']], $options);
    $this->assertEqual($replacement, $this->suggestions['foo']->getName());

    // Chainable Lookup -> Suggestion tokens.
    $replacement = $token_service->replace('[suggestion:lookup:foo]', [], $options);
    $this->assertEqual($replacement, $this->suggestions['foo']->getSuggestion());
    $replacement = $token_service->replace('[suggestion:lookup:foo:name]', [], $options);
    $this->assertEqual($replacement, $this->suggestions['foo']->getName());

    // Chainable Lookup -> Entity tokens.
    $replacement = $token_service->replace('[suggestion:lookup:foo:entity:nid]', [], $options);
    $this->assertEqual($replacement, $this->testNode->id());
    $replacement = $token_service->replace('[suggestion:lookup:foo:entity:url]', [], $options);
    $this->assertEqual($replacement, $this->testNode->toUrl('canonical', ['absolute' => TRUE])->toString());

    // Tests invalide token for node.
    $replacement = $token_service->replace('[suggestion:lookup:foo:entity]', [], $options);
    $this->assertEqual($replacement, '[suggestion:lookup:foo:entity]');
  }

  /**
   * Test the token works with unexisting lookup suggestion.
   */
  public function testSuggestionTokenUnexistingSuggestion() {
    $token_service = \Drupal::token();
    $language_interface = \Drupal::languageManager()->getCurrentLanguage();

    // Tokens options.
    $options = ['langcode' => $language_interface->getId()];

    // Tests inexisting suggestion.
    $replacement = $token_service->replace('[suggestion:lookup:baz:entity:id]', [], $options);
    $this->assertEqual($replacement, '[suggestion:lookup:baz:entity:id]');
  }

  /**
   * Test the token works with unused lookup suggestion.
   */
  public function testSuggestionTokenUnusedSuggestion() {
    $token_service = \Drupal::token();
    $language_interface = \Drupal::languageManager()->getCurrentLanguage();

    // Tokens options.
    $options = ['langcode' => $language_interface->getId()];

    // Tests existing but non-used suggestion.
    $replacement = $token_service->replace('[suggestion:lookup:bar:entity:id]', [], $options);
    $this->assertEqual($replacement, '[suggestion:lookup:bar:entity:id]');
  }

}
