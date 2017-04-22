<?php

namespace Drupal\template_whisperer\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for Template Whisperer Suggestion deletion.
 */
class TemplateWhispererSuggestionDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the suggestion "%name"?', ['%name' => $this->entity->getName()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.template_whisperer_suggestion.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->getQuestion();
    // @TODO: use the "usage" before allowing deletion.
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $t_args = ['%name' => $this->entity->getName()];
    drupal_set_message($this->t('The suggestion "%name" has been deleted.', $t_args));
    $this->logger('template_whisperer')->notice('Deleted suggestion "%name".', $t_args);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
