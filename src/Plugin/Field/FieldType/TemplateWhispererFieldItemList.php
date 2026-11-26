<?php

namespace Drupal\template_whisperer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;

/**
 * Represents a list of Template Whisperer field items.
 */
class TemplateWhispererFieldItemList extends FieldItemList {

  /**
   * {@inheritdoc}
   *
   * The field items maintain the suggestions usage in their own postSave(),
   * which is never called once the field is emptied: the usage of the removed
   * suggestions is then released here.
   */
  public function postSave($update) {
    $result = parent::postSave($update);

    if (!$update || !$this->isEmpty()) {
      return $result;
    }

    $entity = $this->getEntity();
    $langcode = $this->getLangcode();
    $original = method_exists($entity, 'getOriginal') ? $entity->getOriginal() : $entity->original;
    if (!$original || !$original->hasTranslation($langcode)) {
      return $result;
    }

    $twManager = \Drupal::service('plugin.manager.template_whisperer');
    $twSuggestionUsage = \Drupal::service('template_whisperer.suggestion.usage');

    foreach ($original->getTranslation($langcode)->get($this->getName()) as $item) {
      $suggestion = $item->target_id ? $twManager->getOneBySuggestion($item->target_id) : NULL;
      if ($suggestion) {
        $twSuggestionUsage->delete($suggestion, 'template_whisperer', $entity->getEntityTypeId(), $entity->id());
      }
    }

    return $result;
  }

}
