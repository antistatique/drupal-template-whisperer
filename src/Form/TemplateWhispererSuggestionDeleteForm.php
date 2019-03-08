<?php

namespace Drupal\template_whisperer\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\template_whisperer\TemplateWhispererSuggestionUsage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for Template Whisperer Suggestion deletion.
 */
class TemplateWhispererSuggestionDeleteForm extends EntityConfirmFormBase {

  /**
   * Template Whisperer Suggestion Usage.
   *
   * @var \Drupal\template_whisperer\TemplateWhispererSuggestionUsage
   */
  protected $twSuggestionUsage;

  /**
   * Class constructor.
   */
  public function __construct(TemplateWhispererSuggestionUsage $tw_suggestion_usage) {
    $this->twSuggestionUsage = $tw_suggestion_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
    $container->get('template_whisperer.suggestion.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the suggestion "@name"?', ['@name' => $this->entity->getName()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $usages = $this->twSuggestionUsage->countUsage($this->entity);

    if ($usages > 0) {
      $usages = $this->formatPlural($usages, '1 place', '@count places');
      $usage_url = Url::fromRoute('entity.template_whisperer_suggestion.usage', ['template_whisperer_suggestion' => $this->entity->id()]);

      return $this->t('The suggestion "@name" is used in <a href="@usage-url">@usages</a>. Any usage of this suggestion will be lost. This action cannot be undone.', [
        '@name'      => $this->entity->getName(),
        '@usage-url' => $usage_url->toString(),
        '@usages'    => $usages,
      ]);
    }

    return $this->t('This action cannot be undone.');
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

    $this->messenger()->addMessage($this->t('The suggestion "%name" has been deleted.', $t_args));
    $this->logger('template_whisperer')->notice('Deleted suggestion "%name".', $t_args);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
