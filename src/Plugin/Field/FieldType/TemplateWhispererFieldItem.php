<?php

namespace Drupal\template_whisperer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Plugin implementation of the 'template_whisperer' field type.
 *
 * @FieldType(
 *   id = "template_whisperer",
 *   label = @Translation("Template Whisperer"),
 *   description = @Translation("This field stores a Template Whisperer entity reference."),
 *   category = @Translation("General"),
 *   default_widget = "template_whisperer",
 *   default_formatter = "template_whisperer",
 * )
 */
class TemplateWhispererFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'target_id' => array(
          'type' => 'int',
          'not null' => FALSE,
        ),
      ),
      'indexes' => array(
        'target_id' => array('target_id'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['target_id'] = DataDefinition::create('string')
      ->setLabel(t('Template Whisperer reference'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $target_id = $this->get('target_id')->getValue();
    return $target_id === NULL || $target_id === '';
  }

}
