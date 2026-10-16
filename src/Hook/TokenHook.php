<?php

namespace Drupal\template_whisperer\Hook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\Token;
use Drupal\template_whisperer\TemplateWhispererManager;
use Drupal\template_whisperer\TemplateWhispererSuggestionUsage;

/**
 * Hook implementations providing the Template Whisperer tokens.
 */
final class TokenHook {

  use StringTranslationTrait;

  /**
   * Constructs a new TokenHook object.
   *
   * @param \Drupal\template_whisperer\TemplateWhispererManager $twManager
   *   The Template Whisperer manager.
   * @param \Drupal\template_whisperer\TemplateWhispererSuggestionUsage $twSuggestionUsage
   *   The Template Whisperer suggestion usage service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(
    protected TemplateWhispererManager $twManager,
    protected TemplateWhispererSuggestionUsage $twSuggestionUsage,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected Token $token,
  ) {
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
