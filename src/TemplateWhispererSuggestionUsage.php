<?php

namespace Drupal\template_whisperer;

use Drupal\Core\Database\Connection;
use Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface;

/**
 * Defines the class for database Template Whisperer Suggestion usage backend.
 */
class TemplateWhispererSuggestionUsage {

  /**
   * The database connection used to store usage information(s).
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The name of the SQL table used to store file usage information.
   *
   * @var string
   */
  protected $tableName;

  /**
   * Construct the TemplateWhispererSuggestionUsage.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to store
   *   the Template Whisperer Suggestion usage information.
   * @param string $table
   *   (optional) The table to store Template Whisperer Suggestion usage info.
   *   Defaults to 'template_whisperer.suggestion.usage'.
   */
  public function __construct(Connection $connection, $table = 'template_whisperer_suggestion_usage') {
    $this->connection = $connection;

    $this->tableName = $table;
  }

  /**
   * Records that a module is using a Template Whisperer suggestion.
   *
   * Examples:
   * - A module that associates suggestion with nodes, so $type would be
   *   'node' and $id would be the node's nid.
   * - The User module associates an suggestion with a user,
   *   so $type would be 'user' and the $id would be the user's uid.
   *
   * @param \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface $suggestion
   *   A suggestion entity.
   * @param string $module
   *   The name of the module using the suggestion.
   * @param string $type
   *   The type of the object that contains the referenced suggestion.
   * @param int $id
   *   The unique, ID of the object containing the referenced suggestion.
   * @param int $count
   *   (optional) The number of references to add to the object. Defaults to 1.
   */
  public function add(TemplateWhispererSuggestionEntityInterface $suggestion, $module, $type, $id, $count = 1) {
    $this->connection->merge($this->tableName)
      ->keys([
        'sid'    => $suggestion->id(),
        'module' => $module,
        'type'   => $type,
        'id'     => $id,
      ])
      ->fields(['count' => $count])
      ->expression('count', 'count + :count', [':count' => $count])
      ->execute();
  }

  /**
   * Removes a record to indicate that a module is no longer using a suggestion.
   *
   * @param \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface $suggestion
   *   A suggestion entity.
   * @param string $module
   *   The name of the module using the suggestion.
   * @param string $type
   *   (optional) The type of the object that contains the referenced
   *   suggestion. May be omitted if all module references to a suggestion
   *   are being deleted. Defaults to NULL.
   * @param int $id
   *   (optional) The unique, ID of the object containing the referenced
   *   suggestion. May be omitted if all module references to a suggestion
   *   are being deleted. Defaults to NULL.
   * @param int $count
   *   (optional) The number of references to delete from the object. Defaults
   *   to 1. Zero may be specified to delete all references to the suggestion
   *   within a specific object.
   */
  public function delete(TemplateWhispererSuggestionEntityInterface $suggestion, $module, $type = NULL, $id = NULL, $count = 1) {
    // Delete rows that have a exact or less value to prevent empty rows.
    $query = $this->connection->delete($this->tableName)
      ->condition('module', $module)
      ->condition('sid', $suggestion->id());
    if ($type && $id) {
      $query
        ->condition('type', $type)
        ->condition('id', $id);
    }
    if ($count) {
      $query->condition('count', $count, '<=');
    }
    $result = $query->execute();

    // If the row has more than the specified count decrement it by that number.
    if (!$result && $count > 0) {
      $query = $this->connection->update($this->tableName)
        ->condition('module', $module)
        ->condition('sid', $suggestion->id());
      if ($type && $id) {
        $query
          ->condition('type', $type)
          ->condition('id', $id);
      }
      $query->expression('count', 'count - :count', [':count' => $count]);
      $query->execute();
    }
  }

  /**
   * Determines where a suggestion is used.
   *
   * @param \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface $suggestion
   *   A suggestion entity.
   *
   * @return array
   *   An array with usage data.
   */
  public function listUsage(TemplateWhispererSuggestionEntityInterface $suggestion) {
    $result = $this->connection->select($this->tableName, 'tws')
      ->fields('tws', ['module', 'type', 'id', 'count'])
      ->condition('sid', $suggestion->id())
      ->condition('count', 0, '>')
      ->execute();

    return $result->fetchAll();
  }

  /**
   * Determines how many time a suggestion is used.
   *
   * @param \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface $suggestion
   *   A suggestion entity.
   *
   * @return int
   *   Number of reference to the given suggestion.
   */
  public function countUsage(TemplateWhispererSuggestionEntityInterface $suggestion) {
    $count = 0;
    $result = $this->connection->select($this->tableName, 'tws')
      ->fields('tws', ['module', 'type', 'id', 'count'])
      ->condition('sid', $suggestion->id())
      ->condition('count', 0, '>')
      ->execute();
    foreach ($result as $usage) {
      $count += (int) $usage->count;
    }

    return $count;
  }

}
