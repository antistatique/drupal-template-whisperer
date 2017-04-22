<?php

namespace Drupal\template_whisperer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Template Whisperer Suggestion entities.
 *
 * @ingroup template_whisperer
 */
interface TemplateWhispererSuggestionEntityInterface extends ConfigEntityInterface {

  /**
   * Get the name.
   *
   * The human-readable name.
   *
   * @return string
   *   Return the name.
   */
  public function getName();

  /**
   * Set the name.
   *
   * The human-readable name.
   *
   * @param string $name
   *   The name to be setted.
   */
  public function setName($name);

  /**
   * Get the suggestion.
   *
   * A unique suggestion.
   * It only contains lowercase letters, numbers, and underscores.
   *
   * @return string
   *   Return the suggestion.
   */
  public function getSuggestion();

  /**
   * Set the suggestion.
   *
   * A unique suggestion.
   * It only contains lowercase letters, numbers, and underscores.
   *
   * @param string $suggestion
   *   The suggestion to be setted.
   */
  public function setSuggestion($suggestion);

}
