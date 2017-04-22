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
    return [
      'columns' => [
        'target_id' => [
          'type' => 'varchar',
          'length' => 128,
          'not null' => FALSE,
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
    ];
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

  /**
   * {@inheritdoc}
   */
  public function referencedEntities() {
    $referenced_entities = [];
    if ($this->get('target_id')) {
      $twSuggestionStorage = \Drupal::service('entity_type.manager')->getStorage('template_whisperer_suggestion');
      $entity = $twSuggestionStorage->load($this->get('target_id')->getValue());

      if ($entity) {
        $referenced_entities[] = $entity;
      }
    }
    return $referenced_entities;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
    $entity = $this->getEntity();

    $twSuggestionUsage = \Drupal::service('template_whisperer.suggestion.usage');
    if (!$update) {
      // Add a new usage for newly changed suggestions.
      foreach ($this->referencedEntities() as $suggestion) {
        $twSuggestionUsage->add($suggestion, 'template_whisperer', $entity->getEntityTypeId(), $entity->id());
      }
    }
    else {
      // Get current target suggestion entities and suggestion IDs.
      $suggestions = $this->referencedEntities();
      $ids = [];

      /** @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface $suggestion */
      foreach ($suggestions as $suggestion) {
        $ids[] = $suggestion->id();
      }

      // Get the suggestion IDs attached to the field before this update.
      $field_name = $this->getFieldDefinition()->getName();
      $original_ids = [];
      $langcode = $this->getLangcode();
      $original = $entity->original;
      if ($original->hasTranslation($langcode)) {
        $original_items = $original->getTranslation($langcode)->{$field_name};
        foreach ($original_items as $item) {
          $original_ids[] = $item->target_id;
        }
      }

      // Decrement suggestion usage by 1 for suggestions that were
      // removed from the field.
      $removed_ids = array_filter(array_diff($original_ids, $ids));
      if (!empty($removed_ids)) {
        $removed_suggestions = \Drupal::service('entity_type.manager')->getStorage('template_whisperer')->loadMultiple($removed_ids);
        foreach ($removed_suggestions as $suggestion) {
          $twSuggestionUsage->delete($suggestion, 'template_whisperer', $entity->getEntityTypeId(), $entity->id());
        }
      }

      // Add new usage entries for newly added suggestions.
      foreach ($suggestions as $suggestion) {
        if (!in_array($suggestion->id(), $original_ids)) {
          $twSuggestionUsage->add($suggestion, 'template_whisperer', $entity->getEntityTypeId(), $entity->id());
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    parent::delete();
    $entity = $this->getEntity();

    $twSuggestionUsage = \Drupal::service('template_whisperer.suggestion.usage');

    // If a translation is deleted only decrement the suggestion usage by one.
    // If the default translation is deleted remove all suggestion usages
    // within this entity.
    $count = $entity->isDefaultTranslation() ? 0 : 1;
    foreach ($this->referencedEntities() as $suggestion) {
      $twSuggestionUsage->delete($suggestion, 'template_whisperer', $entity->getEntityTypeId(), $entity->id(), $count);
    }
  }

}
