<?php

namespace Drupal\template_whisperer\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Template Whisperer Entity entities.
 */
class TemplateWhispererEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
