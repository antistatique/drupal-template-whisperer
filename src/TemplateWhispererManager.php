<?php

namespace Drupal\template_whisperer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class TemplateWhispererManager.
 */
class TemplateWhispererManager {

  /**
   * EntityTypeManagerInterface to manage Template Whisperer Suggestion.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $suggestionStorage;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity
   *   The interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity) {
    $this->suggestionStorage = $entity->getStorage('template_whisperer_suggestion');
  }

  /**
   * Retrieve the whole list of Template Whisperer entities.
   *
   * @return array
   *   Return an array of
   *   Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity
   */
  public function getList() {
    $list = [];

    $ids = $this->suggestionStorage->getQuery()
      ->execute();

    if (!empty($ids)) {
      $entities = $this->suggestionStorage->loadMultiple($ids);

      foreach ($entities as $entity) {
        $list[$entity->id()] = $entity->getName();
      }
    }

    return $list;
  }

  /**
   * Retrieve the Template Whisperer entity according the given suggestion.
   *
   * @param string $suggestion
   *   The suggestion.
   *
   * @return Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity|null
   *   Return the Entity corresponding of the given suggestion or Null.
   */
  public function getOneBySuggestion($suggestion) {
    $id = $this->suggestionStorage->getQuery()
      ->condition('suggestion', $suggestion)
      ->range(0, 1)
      ->execute();

    return $id ? $this->suggestionStorage->load(current($id)) : NULL;
  }

  /**
   * Extracts all suggestions of a given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity to extract suggestions from.
   *
   * @return array
   *   Array of suggestions.
   */
  public function suggestionsFromEntity(ContentEntityInterface $entity) {
    $suggestions = [];

    $fields = $this->getFields($entity);

    /* @var FieldConfig $field_info */
    foreach ($fields as $field_name => $field_info) {
      // Get the suggestions from this field.
      $suggestions[] = $this->getFieldSuggestions($entity, $field_name);
    }

    return $suggestions;
  }

  /**
   * Returns a list of the template_whisperer fields on an entity.
   *
   * @param Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Entity which contain our special field(s).
   *
   * @return array
   *   The suggestions needed for the given Entity.
   */
  protected function getFields(ContentEntityInterface $entity) {
    $field_list = [];

    if ($entity instanceof ContentEntityInterface) {

      // Get a list of the Template Whisperer field types.
      $field_types = $this->fieldTypes();

      // Get a list of the field definitions on this entity.
      $definitions = $entity->getFieldDefinitions();

      // Iterate through all the fields looking for ones of Template Whisperer.
      foreach ($definitions as $field_name => $definition) {
        // Get the field type, ie: template_whisperer.
        $field_type = $definition->getType();

        // Check the field type against our list of fields.
        if (isset($field_type) && in_array($field_type, $field_types)) {
          $field_list[$field_name] = $definition;
        }
      }
    }

    return $field_list;
  }

  /**
   * Returns a list of the suggestions values from a field.
   *
   * @param Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Entity that contains our special field(s).
   * @param string $field_name
   *   The field that contains our suggestion.
   *
   * @return string
   *   The suggestion string.
   */
  protected function getFieldSuggestions(ContentEntityInterface $entity, $field_name) {
    $suggestion = '';
    foreach ($entity->{$field_name} as $item) {
      // Get value and break it into an array of suggestions with values.
      $target_id = $item->get('target_id')->getValue();
      if (!empty($target_id)) {
        $whisperer = $this->suggestionStorage->load($target_id);
        $suggestion = $whisperer->getSuggestion();
      }
    }

    return $suggestion;
  }

  /**
   * Returns a list of fields handled by Template Whisperer.
   *
   * @return array
   *   The list of fields types.
   */
  protected function fieldTypes() {
    // @TODO: Either get this dynamically from field plugins or forget it and just hardcode template_whisperer where this is called.
    return ['template_whisperer'];
  }

}
