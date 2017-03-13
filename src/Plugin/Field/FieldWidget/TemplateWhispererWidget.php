<?php

namespace Drupal\template_whisperer\Plugin\Field\FieldWidget;

use \Drupal\Core\Field\FieldItemListInterface;
use \Drupal\Core\Field\WidgetBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\template_whisperer\TemplateWhispererManager;

/**
 * Plugin implementation of the 'template_whisperer' widget.
 *
 * @FieldWidget(
 *   id = "template_whisperer",
 *   label = @Translation("Advanced Template Whisperer form"),
 *   field_types = {
 *     "template_whisperer"
 *   }
 * )
 */
class TemplateWhispererWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Template Whisperer Manager.
   *
   * @var Drupal\template_whisperer\TemplateWhispererManager
   */
  protected $twManager;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, TemplateWhispererManager $twManager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->twManager = $twManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
    $plugin_id,
    $plugin_definition,
    $configuration['field_definition'],
    $configuration['settings'],
    $configuration['third_party_settings'],
    $container->get('plugin.manager.template_whisperer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $whisperers = $this->twManager->getList();

    // Add the outer fieldset.
    $element += [
      '#type' => 'details',
    ];

    // Create the field.
    $entity = $items->getEntity()->getEntityTypeId();
    $bundle = $items->getEntity()->getType();

    $target_id = $items[$delta]->get('target_id')->getValue();
    $element['target_id'] = array(
      '#title' => $this->t('Select a template'),
      '#type' => 'select',
      '#options' => $whisperers,
      '#empty_value' => '',
      '#default_value' => (isset($target_id)) ? $target_id : NULL,
      '#description' => $this->t('Specify a template which will be used to render the content.'),
    );

    // Put the form element into the form's "advanced" group.
    $element['#group'] = 'advanced';

    return $element;
  }

}
