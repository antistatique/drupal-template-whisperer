<?php

namespace Drupal\template_whisperer\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\template_whisperer\TemplateWhispererManager;

/**
 * Form controller for Template Whisperer forms.
 *
 * @ingroup template_whisperer
 */
class TemplateWhispererForm extends ContentEntityForm {

  /**
   * Template Whisperer Manager.
   *
   * @var Drupal\template_whisperer\TemplateWhispererManager
   */
  protected $twManager;

  /**
   * Class constructor.
   */
  public function __construct(TemplateWhispererManager $twManager) {
    $this->twManager = $twManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
    $container->get('plugin.manager.template_whisperer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $suggestion = $form_state->getValue('suggestion')[0]['value'];

    if (empty($suggestion) || preg_match('@^_+$@', $suggestion)) {
      $form_state->setErrorByName('suggestion', $this->t('The suggestion must contain unique characters.'));
    }

    if (preg_match('@[^a-z0-9_]+@', $suggestion)) {
      $form_state->setErrorByName('suggestion', $this->t('The suggestion must contain only lowercase letters, numbers, and underscores.'));
    }

    $entity = $this->twManager->getOneBySuggestion($suggestion);
    if (!empty($entity) && $this->entity->id() != $entity->id()) {
      $form_state->setErrorByName('suggestion', $this->t('The suggestion is already in use. It must be unique.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the "%label" Template Whisperer.', [
          '%label' => $entity->getName(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the "%label" Template Whisperer.', [
          '%label' => $entity->getName(),
        ]));
    }
    $form_state->setRedirect('template_whisperer');
  }

}
