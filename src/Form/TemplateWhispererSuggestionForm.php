<?php

namespace Drupal\template_whisperer\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\template_whisperer\TemplateWhispererManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Template Whisperer forms.
 *
 * @ingroup template_whisperer
 */
class TemplateWhispererSuggestionForm extends EntityForm {

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
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity $entity */
    $entity = $this->buildEntity($form, $form_state);

    $form['#title'] = $this->operation == 'add' ? $this->t('Add suggestion')
        :
        $this->t('Edit %name suggestion', ['%name' => $entity->name]);

    $form['name'] = [
      '#title'         => $this->t('Name'),
      '#type'          => 'textfield',
      '#default_value' => $entity->name ?? NULL,
      '#description'   => $this->t('The human-readable name. Will appear in the field widget.'),
      '#size'          => 50,
      '#required'      => TRUE,
    ];

    $form['suggestion'] = [
      '#title'         => $this->t('Suggestion'),
      '#type'          => 'textfield',
      '#default_value' => $entity->suggestion ?? NULL,
      '#description'   => $this->t('A unique suggestion for this template whisperer. It must only contain lowercase letters, numbers, and underscores. E.g. <code>news_list</code>'),
      '#required'      => TRUE,
      '#size'          => 50,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $suggestion = $form_state->getValue('suggestion');

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
    /** @var \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntity $entity */
    $entity = $this->buildEntity($form, $form_state);

    $entity->id = trim($entity->suggestion);
    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the "%name" suggestion.', [
          '%name' => $entity->getName(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the "%name" suggestion.', [
          '%name' => $entity->getName(),
        ]));
        break;
    }
    $form_state->setRedirect('entity.template_whisperer_suggestion.collection');
  }

}
