<?php

namespace Drupal\template_whisperer\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'template_whisperer' formatter.
 */
#[FieldFormatter(
  id: "template_whisperer",
  label: new TranslatableMarkup("Template Whisperer Formatter"),
  field_types: ["template_whisperer"],
)]
class TemplateWhispererFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Does not actually output anything.
    return [];
  }

}
