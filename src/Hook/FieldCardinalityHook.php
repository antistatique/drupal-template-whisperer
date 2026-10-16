<?php

namespace Drupal\template_whisperer\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook removing the cardinality setting of the Template Whisperer field.
 */
final class FieldCardinalityHook {

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

}
