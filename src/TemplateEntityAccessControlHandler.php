<?php

namespace Drupal\template_whisperer;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Template Entity entity.
 *
 * @see \Drupal\template_whisperer\Entity\TemplateEntity.
 */
class TemplateEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\template_whisperer\Entity\TemplateEntityInterface $entity */
    switch ($operation) {
    case 'view':
    case 'update':
    case 'edit':
    case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer template_whisperer entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
      dump($account);
      die();
    return AccessResult::allowedIfHasPermission($account, 'administer template_whisperer entities');
  }

}
