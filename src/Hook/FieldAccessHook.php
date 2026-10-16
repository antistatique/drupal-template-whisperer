<?php

namespace Drupal\template_whisperer\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;

/**
 * Hook implementations to control access to the Template Whisperer field.
 */
final class FieldAccessHook {

  /**
   * Implements hook_entity_field_access().
   *
   * Asserts that Template Whisperer fields aren't edited by those who
   * shouldn't.
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

}
