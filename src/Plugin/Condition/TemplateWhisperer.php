<?php

namespace Drupal\template_whisperer\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\template_whisperer\TemplateWhispererManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Condition\Annotation\Condition;
use Drupal\Core\Annotation\ContextDefinition;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a 'Template Whisperer' condition.
 *
 * @Condition(
 *   id = "template_whisperer",
 *   label = @Translation("Template Whisperer"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class TemplateWhisperer extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\template_whisperer\TemplateWhispererManager
   *    The Template Manager.
   */
  protected $templateWhispererManager;

  /**
   * Creates a new NodeType instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\template_whisperer\TemplateWhispererManager $template_whisperer_manager
   *   The Template Whisperer Manager.
   */
  public function __construct(EntityStorageInterface $entity_storage, array $configuration, $plugin_id, $plugin_definition, TemplateWhispererManager $template_whisperer_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->templateWhispererManager = $template_whisperer_manager;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array                                                     $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string                                                    $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed                                                     $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity.manager')->getStorage('node_type'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.template_whisperer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = $this->templateWhispererManager->getList();

    $form['suggestions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('When the node has the following Suggestion(s)'),
      '#default_value' => $this->configuration['suggestions'],
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', $options),
      '#description' => $this->t('Select suggestion(s) to enforce only on those selected. If none are selected, all suggestion will be allowed.'),
    ];


    $form = parent::buildConfigurationForm($form, $form_state);
    unset($form['negate']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'suggestions' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['suggestions'] = array_filter($form_state->getValue('suggestions'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['suggestions'])) {
      return TRUE;
    }

    $node = $this->getContextValue('node');
    $nodeTemplates = $this->templateWhispererManager->suggestionsFromEntity($node);

    return count(array_intersect($this->configuration['suggestions'], $nodeTemplates)) > 0;
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    $templates = $this->configuration['suggestions'];
    return $this->t('The node template is @template', [ '@template' => implode(', ', $templates) ]);
  }

}
