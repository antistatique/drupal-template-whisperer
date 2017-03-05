<?php

namespace Drupal\template_whisperer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'template_whisperer' formatter.
 *
 * @FieldFormatter(
 *   id = "template_whisperer",
 *   module = "template_whisperer",
 *   label = @Translation("Template Whisperer Formatter"),
 *   field_types = {
 *     "template_whisperer"
 *   }
 * )
 */
class TemplateWhispererFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Does not actually output anything.
    return [];
  }

}
