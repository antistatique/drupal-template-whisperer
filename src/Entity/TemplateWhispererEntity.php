<?php

namespace Drupal\template_whisperer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Template Whisperer Entity entity.
 *
 * @ingroup template_whisperer
 *
 * @ConfigEntityType(
 *   id = "template_whisperer",
 *   label = @Translation("Template Whisperer Entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\template_whisperer\TemplateWhispererListBuilder",
 *     "form" = {
 *       "add" = "Drupal\template_whisperer\Form\TemplateWhispererForm",
 *       "edit" = "Drupal\template_whisperer\Form\TemplateWhispererForm",
 *       "delete" = "Drupal\template_whisperer\Form\TemplateWhispererDeleteForm",
 *     },
 *   },
 *   config_prefix = "template_whisperer",
 *   admin_permission = "administer template_whisperer entities",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/template-whisperer/{template_whisperer}",
 *     "add-form" = "/admin/structure/template-whisperer/add",
 *     "edit-form" = "/admin/structure/template-whisperer/{template_whisperer}/edit",
 *     "delete-form" = "/admin/structure/template-whisperer/{template_whisperer}/delete",
 *     "collection" = "/admin/structure/template-whisperer/list",
 *   },
 * )
 */
class TemplateWhispererEntity extends ConfigEntityBase implements TemplateWhispererEntityInterface {

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
