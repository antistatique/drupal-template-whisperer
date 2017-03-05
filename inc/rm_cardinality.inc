<?php

/**
 * @file
 * Contains hook to remove cardinality on the Template Whisperer field.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter() for 'field_storage_config_edit_form'.
 */
function template_whisperer_form_field_storage_config_edit_form_alter(&$form, FormStateInterface $form_state) {
  if ($form_state->getFormObject()->getEntity()->getType() == 'template_whisperer') {
    // Hide the cardinality field.
    $form['cardinality_container']['#access'] = FALSE;
    $form['cardinality_container']['#disabled'] = TRUE;
  }
}
