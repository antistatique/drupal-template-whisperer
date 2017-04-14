<?php

namespace Drupal\template_whisperer\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for template_whisperer type deletion.
 */
class TemplateWhispererDeleteForm extends EntityConfirmFormBase {

  /**
   * The query factory to create entity queries.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * String translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translation;

  /**
   * Constructs a new TemplateWhispererDeleteForm object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query object.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation manager.
   */
  public function __construct(QueryFactory $query_factory, TranslationInterface $string_translation) {
    $this->queryFactory = $query_factory;
    $this->translation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the suggestion "%name"?', array('%name' => $this->entity->getName()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.template_whisperer.collection');
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
    $t_args = array('%name' => $this->entity->getName());
    drupal_set_message($this->t('The suggestion "%name" has been deleted.', $t_args));
    $this->logger('template_whisperer')->notice('Deleted suggestion "%name".', $t_args);

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
