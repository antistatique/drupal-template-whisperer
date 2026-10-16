<?php

namespace Drupal\template_whisperer\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\Core\Url;
use Drupal\template_whisperer\TemplateWhispererManager;
use Drupal\template_whisperer\TemplateWhispererSuggestionUsage;

/**
 * Hook implementations for the Template Whisperer module.
 */
final class TemplateWhispererHooks {

  use StringTranslationTrait;

  /**
   * Constructs a new TemplateWhispererHooks object.
   *
   * @param \Drupal\template_whisperer\TemplateWhispererManager $twManager
   *   The Template Whisperer manager.
   * @param \Drupal\template_whisperer\TemplateWhispererSuggestionUsage $twSuggestionUsage
   *   The Template Whisperer suggestion usage service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(
    protected TemplateWhispererManager $twManager,
    protected TemplateWhispererSuggestionUsage $twSuggestionUsage,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected RouteMatchInterface $routeMatch,
    protected Token $token,
  ) {
  }

  /**
   * Implements hook_theme_suggestions_alter().
   */
  #[Hook('theme_suggestions_alter')]
  public function themeSuggestionsAlter(array &$suggestions, array $variables, $hook): void {
    $this->themeSuggestionsPage($suggestions, $variables, $hook);
    $this->themeSuggestionsEntity($suggestions, $variables, $hook);
  }

  /**
   * Generate additional suggestions based on the page.
   *
   *   - page--node--1--suggestion.html.twig
   *   - page--node--suggestion.html.twig.
   */
  protected function themeSuggestionsPage(array &$suggestions, array $variables, $hook): void {
    if ($hook !== 'page') {
      return;
    }

    // Get the entity from the Route parameters.
    $entity = NULL;
    foreach ($this->routeMatch->getParameters() as $param) {
      if ($param instanceof ContentEntityInterface) {
        $entity = $param;
        break;
      }
    }

    if (!$entity) {
      return;
    }

    $suggestionsEntity = $this->twManager->suggestionsFromEntity($entity);

    if (empty($suggestionsEntity)) {
      return;
    }

    // Expose one suggestion by fields.
    foreach ($suggestionsEntity as $suggestion) {
      // Collect every suggestions variants that will be injected.
      $base_suggestions = [];

      // Will suggest page--node--1--suggestion.html.twig.
      $base_suggestions[] = [
        $hook,
        $entity->getEntityTypeId(),
        $entity->id(),
      ];

      // Will suggest page--node--suggestion.html.twig.
      $base_suggestions[] = [
        $hook,
        $entity->getEntityTypeId(),
      ];

      // Generate suggestions for every variations.
      foreach ($base_suggestions as $base_suggestion) {
        $suggestions[] = implode('__', $base_suggestion) . '__' . $suggestion;
      }
    }
  }

  /**
   * Generate additional suggestions based on the entity.
   *
   *   - node--1--article--teaser--suggestion.html.twig
   *   - node--article--teaser--suggestion.html.twig
   *   - node--1--article--suggestion.html.twig
   *   - node--article--suggestion.html.twig.
   */
  protected function themeSuggestionsEntity(array &$suggestions, array $variables, $hook): void {
    if (isset($variables['elements']['#entity_type']) && $variables['elements']['#entity_type'] === $hook) {
      $entity = NULL;
      if (isset($variables['elements']['#' . $hook])) {
        $entity = $variables['elements']['#' . $hook];
      }

      if (!empty($entity) && $entity instanceof ContentEntityInterface) {
        $suggestionsEntity = $this->twManager->suggestionsFromEntity($entity);

        if (!empty($suggestionsEntity)) {

          // Expose one suggestion by fields.
          foreach ($suggestionsEntity as $suggestion) {
            // Collect every suggestions variants that will be injected.
            $base_suggestions = [];

            // Will suggest node--article--suggestion.html.twig.
            $base_suggestions[] = [
              $entity->getEntityTypeId(),
              $entity->bundle(),
            ];

            // Will suggest node--1--article--suggestion.html.twig.
            $base_suggestions[] = [
              $entity->getEntityTypeId(),
              $entity->id(),
              $entity->bundle(),
            ];

            // Colect variation based on the view_mode.
            if (isset($variables['elements']['#view_mode'])) {
              // Will suggest node--article--teaser--suggestion.html.twig.
              $base_suggestions[] = [
                $entity->getEntityTypeId(),
                $entity->bundle(),
                $variables['elements']['#view_mode'],
              ];
              // Will suggest node--1--article--teaser--suggestion.html.twig.
              $base_suggestions[] = [
                $entity->getEntityTypeId(),
                $entity->id(),
                $entity->bundle(),
                $variables['elements']['#view_mode'],
              ];
            }

            // Generate suggestions for every variations.
            foreach ($base_suggestions as $base_suggestion) {
              $suggestions[] = implode('__', $base_suggestion) . '__' . $suggestion;
            }
          }
        }
      }
    }
  }

  /**
   * Implements hook_entity_field_access().
   *
   * Asserts that Template Whisperer fields aren't edited by those who shouldn't.
   */
  #[Hook('entity_field_access')]
  public function entityFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, ?FieldItemListInterface $items = NULL): AccessResult {
    // Check access only for Template Whisperer field.
    if ($field_definition->getType() != 'template_whisperer') {
      return AccessResult::neutral();
    }

    // First we'll check if the user has the 'superuser'
    // permissions that entity provides. This way administrators
    // will be able to administer the content types.
    if ($account->hasPermission('bypass node access')) {
      return AccessResult::allowed();
    }
    if ($account->hasPermission('administer content types', $account)) {
      return AccessResult::allowed();
    }

    // For anyone else, it depends on the account permissions.
    if ($account->hasPermission('administer the template whisperer field', $account)) {
      return AccessResult::allowed();
    }

    // Anything else on this field is forbidden.
    return AccessResult::forbidden();
  }

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Main module help for the template_whisperer module.
      case 'help.page.template_whisperer':
        $output = '<h2>' . $this->t('About') . '</h2>';
        $output .= '<p>' . $this->t('Provides a formalized way to declare and suggest page templates using "Template Whisperer" aka "TW" entity.') . '</p>';

        $output .= '<h3>' . $this->t('Intended worflow') . '</h3>';
        $output .= '<p>' . $this->t('The module uses is own field and his own entity to generate more suggestions for your selected nodes.') . '</p>';

        $output .= '<p>' . $this->t('The best way of using Template Whisperer is as follows:') . '</p>';
        $output .= '<ol>';
        $output .= '<li>' . $this->t('Create your own <a href=":add-form">suggestion</a>, fill in the specific name and suggestion you need.', [':add-form' => Url::fromRoute('entity.template_whisperer_suggestion.add_form')->toString()]) . '</li>';
        $output .= '<li>' . $this->t('Add the field "Template Whisperer" field via the field settings for that entity or bundle type.') . '</li>';
        $output .= '<li>' . $this->t('When you edit the content of that entity or bundle you should then see the new "Template Whisperer" section on the Advanced tabs.') . '</li>';
        $output .= '<li>' . $this->t('Select the suggestion you want to add for that entity or bundle.') . '</li>';
        $output .= '<li>' . $this->t('You can now have access to your own suggestion such [entity-type-id]--[entity-type]--<strong>list-news.html.twig</strong>.') . '</li>';

        return $output;

      default:
    }

    return NULL;
  }

  /**
   * Implements hook_form_FORM_ID_alter() for 'field_storage_config_edit_form'.
   */
  #[Hook('form_field_storage_config_edit_form_alter')]
  public function formFieldStorageConfigEditFormAlter(&$form, FormStateInterface $form_state): void {
    if ($form_state->getFormObject()->getEntity()->getType() == 'template_whisperer') {
      // Hide the cardinality field.
      $form['cardinality_container']['#access'] = FALSE;
      $form['cardinality_container']['#disabled'] = TRUE;
    }
  }

  /**
   * Implements hook_token_info().
   */
  #[Hook('token_info')]
  public function tokenInfo(): array {
    $types = [];
    $suggestion = [];
    $suggestion_lookup = [];

    $types['suggestion'] = [
      'name'        => $this->t('Suggestions'),
      'description' => $this->t("Tokens related to individual Template Whisperer's suggestion."),
    ];
    $types['suggestion-lookup'] = [
      'name'        => $this->t('Suggestions Lookup'),
      'description' => $this->t("Suggestions Lookup the given suggestion machine name."),
      'dynamic'     => TRUE,
      'needs-data'  => 'suggestion',
    ];

    // Tokens for Template Whisperer's suggestions.
    $suggestion['sid'] = [
      'name'        => $this->t('Suggestion ID'),
      'description' => $this->t("The unique ID of the suggestion."),
    ];
    $suggestion['name'] = [
      'name'        => $this->t("Display name"),
      'description' => $this->t("The suggestion display name."),
    ];

    // Chained tokens for Template Whisperer's suggestions.
    $suggestion['lookup'] = [
      'name'        => $this->t('Lookup'),
      'description' => $this->t('Lookup the given suggestion.'),
      'dynamic'     => TRUE,
      'type'        => 'suggestion-lookup',
    ];

    $suggestion_lookup['entity'] = [
      'name'        => $this->t('Entity'),
      'description' => $this->t('Get the first entity where the suggestions is used in.'),
      'type'        => 'entity',
    ];

    return [
      'types' => $types,
      'tokens' => [
        'suggestion' => $suggestion,
        'suggestion-lookup' => $suggestion_lookup,
      ],
    ];
  }

  /**
   * Implements hook_tokens().
   */
  #[Hook('tokens')]
  public function tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata): array {
    $replacements = [];

    // Simple key values on the suggestion.
    if ($type == 'suggestion' && !empty($data['suggestion'])) {
      /** @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity */
      $suggestion = $data['suggestion'];

      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'name':
            $replacements[$original] = $suggestion->getName();
            break;

          // When nothing is given, fallback to the sid.
          case 'sid':
          default:
            $replacements[$original] = $suggestion->getSuggestion();
            break;
        }
      }
    }

    // Lookup from dynamic sid.
    $lookup_tokens = $this->token->findWithPrefix($tokens, 'lookup');
    if ($lookup_tokens) {
      $key = key($lookup_tokens);

      $sid = explode(':', $key)[0];

      // If sid found in the string, try to lookup a suggestion entity.
      $suggestion = $this->twManager->getOneBySuggestion($sid);

      if ($suggestion) {
        $replacements += $this->token->generate('suggestion-lookup', $lookup_tokens, ['suggestion' => $suggestion], $options, $bubbleable_metadata);
      }
    }

    // Simple key values on the suggestion-lookup.
    if ($type == 'suggestion-lookup' && !empty($data['suggestion'])) {
      /** @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity */
      $suggestion = $data['suggestion'];
      $usages = $this->twSuggestionUsage->listUsage($suggestion);
      $usage = reset($usages);

      // Prevent lookup when the suggestion is never used.
      if (!$usage) {
        return $replacements;
      }

      $entity_storage = $this->entityTypeManager->getStorage($usage->type);
      $entity = $entity_storage->load($usage->id);

      foreach ($tokens as $name => $original) {
        preg_match('/^.*?:(.*?)(:(.*)$|$)/', $name, $matches);
        $action = $matches[1] ?? '';
        $rest = isset($matches[2]) ? ltrim($matches[2], ':') : '';

        switch ($action) {
          case 'entity':
            $replacements += $this->token->generate($usage->type, [$rest => $original], [$usage->type => $entity], $options, $bubbleable_metadata);
            break;

          // When nothing is given, return to the suggestion token, which will
          // fallback to the sid.
          default:
            $replacements += $this->token->generate('suggestion', [$action => $original], ['suggestion' => $suggestion], $options, $bubbleable_metadata);
            break;
        }
      }
    }

    return $replacements;
  }

}
