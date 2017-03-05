<?php

namespace Drupal\template_whisperer\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Template Whisperer Entity entities.
 *
 * @ingroup template_whisperer
 */
interface TemplateWhispererEntityInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the Template Whisperer Entity name.
   *
   * @return string
   *   Name of the Template Whisperer Entity.
   */
  public function getName();

  /**
   * Sets the Template Whisperer Entity name.
   *
   * @param string $name
   *   The Template Whisperer Entity name.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateWhispererEntityInterface
   *   The called Template Whisperer Entity entity.
   */
  public function setName($name);

  /**
   * Gets the Template Whisperer Entity suggestion.
   *
   * @return string
   *   Machine name of the Template Whisperer Entity suggestion.
   */
  public function getSuggestion();

  /**
   * Sets the Template Whisperer Entity suggestion.
   *
   * @param string $suggestion
   *   The Template Whisperer Entity suggestion.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateWhispererEntityInterface
   *   The called Template Whisperer Entity entity.
   */
  public function setSuggestion($suggestion);

  /**
   * Gets the Template Whisperer Entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Template Whisperer Entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Template Whisperer Entity creation timestamp.
   *
   * @param int $timestamp
   *   The Template Whisperer Entity creation timestamp.
   *
   * @return \Drupal\template_whisperer\Entity\TemplateWhispererEntityInterface
   *   The called Template Whisperer Entity entity.
   */
  public function setCreatedTime($timestamp);

}
