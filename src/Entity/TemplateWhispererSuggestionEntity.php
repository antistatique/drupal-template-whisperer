<?php

namespace Drupal\template_whisperer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Template Whisperer Suggestion entity.
 *
 * @ingroup template_whisperer
 *
 * @ConfigEntityType(
 *   id = "template_whisperer_suggestion",
 *   label = @Translation("Template Whisperer Suggestion Entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\template_whisperer\TemplateWhispererSuggestionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\template_whisperer\Form\TemplateWhispererSuggestionForm",
 *       "edit" = "Drupal\template_whisperer\Form\TemplateWhispererSuggestionForm",
 *       "delete" = "Drupal\template_whisperer\Form\TemplateWhispererSuggestionDeleteForm",
 *     },
 *   },
 *   config_prefix = "template_whisperer_suggestion",
 *   admin_permission = "administer template whisperer suggestion entities",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/template-whisperer/{template_whisperer_suggestion}",
 *     "add-form" = "/admin/structure/template-whisperer/add",
 *     "edit-form" = "/admin/structure/template-whisperer/{template_whisperer_suggestion}/edit",
 *     "delete-form" = "/admin/structure/template-whisperer/{template_whisperer_suggestion}/delete",
 *     "collection" = "/admin/structure/template-whisperer/list",
 *   },
 * )
 */
class TemplateWhispererSuggestionEntity extends ConfigEntityBase implements TemplateWhispererSuggestionEntityInterface {

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

}
