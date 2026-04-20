<?php

namespace Drupal\template_whisperer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\template_whisperer\Form\TemplateWhispererSuggestionDeleteForm;
use Drupal\template_whisperer\Form\TemplateWhispererSuggestionForm;
use Drupal\template_whisperer\TemplateWhispererSuggestionListBuilder;

/**
 * Defines the Template Whisperer Suggestion entity.
 *
 * @ingroup template_whisperer
 */
#[ConfigEntityType(
  id: "template_whisperer_suggestion",
  label: new TranslatableMarkup("Template Whisperer Suggestion Entity"),
  handlers: [
    "view_builder" => EntityViewBuilder::class,
    "list_builder" => TemplateWhispererSuggestionListBuilder::class,
    "form" => [
      "add" => TemplateWhispererSuggestionForm::class,
      "edit" => TemplateWhispererSuggestionForm::class,
      "delete" => TemplateWhispererSuggestionDeleteForm::class,
    ],
  ],
  config_prefix: "template_whisperer_suggestion",
  admin_permission: "administer template whisperer suggestion entities",
  entity_keys: [
    "id" => "id",
  ],
  links: [
    "canonical" => "/admin/structure/template-whisperer/{template_whisperer_suggestion}",
    "add-form" => "/admin/structure/template-whisperer/add",
    "edit-form" => "/admin/structure/template-whisperer/{template_whisperer_suggestion}/edit",
    "delete-form" => "/admin/structure/template-whisperer/{template_whisperer_suggestion}/delete",
    "collection" => "/admin/structure/template-whisperer",
    "usage" => "/admin/structure/template-whisperer/{template_whisperer_suggestion}/usage",
  ],
  config_export: [
    "id",
    "name",
    "suggestion",
  ],
)]
class TemplateWhispererSuggestionEntity extends ConfigEntityBase implements TemplateWhispererSuggestionEntityInterface {

  /**
   * The name used in the Admin UI.
   *
   * @var string
   */
  public $name;

  /**
   * The suggestion used to generate alternatives templates names.
   *
   * @var string
   */
  public $suggestion;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuggestion() {
    return $this->suggestion;
  }

  /**
   * {@inheritdoc}
   */
  public function setSuggestion($suggestion) {
    $this->suggestion = $suggestion;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    foreach ($entities as $entity) {
      $twSuggestionUsage = \Drupal::service('template_whisperer.suggestion.usage');

      // Delete all remaining references to this suggestion.
      $suggestion_usage = $twSuggestionUsage->listUsage($entity);
      if (!empty($suggestion_usage)) {
        foreach ($suggestion_usage as $usage) {
          $twSuggestionUsage->delete($entity, $usage->module);
        }
      }
    }
  }

}
