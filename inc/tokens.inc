<?php

/**
 * @file
 * Token integration for the Template Whisperer module.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function template_whisperer_token_info() {

  $types['suggestion'] = [
    'name'        => t('Suggestions'),
    'description' => t("Tokens related to individual Template Whisperer's suggestion."),
  ];
  $types['suggestion-lookup'] = [
    'name'        => t('Suggestions Lookup'),
    'description' => t("Suggestions Lookup the given suggestion machine name."),
    'dynamic'     => TRUE,
    'needs-data'  => 'suggestion',
  ];

  // Tokens for Template Whisperer's suggestions.
  $suggestion['sid'] = [
    'name'        => t('Suggestion ID'),
    'description' => t("The unique ID of the suggestion."),
  ];
  $suggestion['name'] = [
    'name'        => t("Display name"),
    'description' => t("The suggestion display name."),
  ];

  // Chained tokens for Template Whisperer's suggestions.
  $suggestion['lookup'] = [
    'name'        => t('Lookup'),
    'description' => t('Lookup the given suggestion.'),
    'dynamic'     => TRUE,
    'type'        => 'suggestion-lookup',
  ];

  $suggestion_lookup['entity'] = [
    'name'        => t('Entity'),
    'description' => t('Get the first entity where the suggestions is used in.'),
    'type'        => 'entity',
  ];

  $suggestion_lookup['used-in'] = [
    'name'        => t('Used In'),
    'description' => t('Get the first node where the suggestions is used in.'),
    'type'        => 'array',
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
 * Implements hook_token().
 */
function template_whisperer_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service    = \Drupal::token();
  $tw_manager       = \Drupal::service('plugin.manager.template_whisperer');
  $tw_manager_usage = \Drupal::service('template_whisperer.suggestion.usage');

  $replacements = [];

  // Simple key values on the suggestion.
  if ($type == 'suggestion' && !empty($data['suggestion'])) {
    /* @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity */
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
  if ($lookup_tokens = $token_service->findWithPrefix($tokens, 'lookup')) {
    $key = key($lookup_tokens);

    $sid = explode(':', $key)[0];

    // If sid found in the string, try to lookup a suggestion entity.
    $suggestion = $tw_manager->getOneBySuggestion($sid);

    if ($suggestion) {
      $replacements += $token_service->generate('suggestion-lookup', $lookup_tokens, ['suggestion' => $suggestion], $options, $bubbleable_metadata);
    }
  }

  // Simple key values on the suggestion-lookup.
  if ($type == 'suggestion-lookup' && !empty($data['suggestion'])) {
    /* @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity */
    $suggestion = $data['suggestion'];
    $usages = $tw_manager_usage->listUsage($suggestion);
    $usage = reset($usages);

    $entity_type_manager = \Drupal::service('entity_type.manager');
    $entity_storage = $entity_type_manager->getStorage($usage->type);
    $entity = $entity_storage->load($usage->id);

    foreach ($tokens as $name => $original) {
      preg_match('/^.*?:(.*?)(:(.*)$|$)/', $name, $matches);
      $action = isset($matches[1]) ? $matches[1] : '';
      $rest = isset($matches[2]) ? ltrim($matches[2], ':') : '';

      switch ($action) {
        case 'entity':
          $replacements += $token_service->generate($usage->type, [$rest => $original], [$usage->type => $entity], $options, $bubbleable_metadata);
          break;

        // When nothing is given, return to the suggestion token, which will
        // fallback to the sid.
        default:
          $replacements += $token_service->generate('suggestion', [$action => $original], ['suggestion' => $suggestion], $options, $bubbleable_metadata);
          break;
      }
    }
  }

  return $replacements;
}
