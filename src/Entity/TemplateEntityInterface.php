<?php

namespace Drupal\template_whisperer\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Template Entity entities.
 *
 * @ingroup template_whisperer
 */
interface TemplateEntityInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the Template Entity name.
   *
   * @return string
   *   Name of the Template Entity.
   */
  public function getName();

  /**
   * Sets the Template Entity name.
   *
   * @param string $name
   *   The Template Entity name.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateEntityInterface
   *   The called Template Entity entity.
   */
  public function setName($name);

  /**
   * Gets the Template Entity suggestion.
   *
   * @return string
   *   Machine name of the Template Entity suggestion.
   */
  public function getSuggestion();

  /**
   * Sets the Template Entity suggestion.
   *
   * @param string $suggestion
   *   The Template Entity suggestion.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateEntityInterface
   *   The called Template Entity entity.
   */
  public function setSuggestion($suggestion);

  /**
   * Gets the Template Entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Template Entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Template Entity creation timestamp.
   *
   * @param int $timestamp
   *   The Template Entity creation timestamp.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateEntityInterface
   *   The called Template Entity entity.
   */
  public function setCreatedTime($timestamp);

}
