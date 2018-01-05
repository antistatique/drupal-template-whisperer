<?php

namespace Drupal\template_whisperer\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\template_whisperer\TemplateWhispererManager;

/**
 * Plugin implementation of the 'template_whisperer' widget.
 *
 * @FieldWidget(
 *   id = "template_whisperer",
 *   label = @Translation("Advanced Template Whisperer"),
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
    // Get all the suggestions.
    $suggestions = $this->twManager->getList();
    $field_settings = $this->getFieldSettings();

    // Filter the possible value only if at least one element has been checked.
    // Otherwise, all the suggestions are available.
    if (isset($field_settings['handler']) && isset($field_settings['handler']['suggestions'])) {
      $filtred_suggestions = array_filter($field_settings['handler']['suggestions'], function ($value) {
        return ($value != '0');
      });

      // Filter the suggestions to keep only the selected one in the field conf.
      if (!empty($filtred_suggestions)) {
        $suggestions = array_filter($suggestions, function ($key) use ($filtred_suggestions) {
          return array_key_exists($key, $filtred_suggestions);
        }, ARRAY_FILTER_USE_KEY);
      }
    }

    // Add the outer fieldset.
    $element += [
      '#type' => 'details',
    ];

    $target_id = $items[$delta]->get('target_id')->getValue();
    $element['target_id'] = [
      '#title' => $this->t('Select a template'),
      '#type' => 'select',
      '#options' => $suggestions,
      '#empty_value' => '',
      '#default_value' => (isset($target_id)) ? $target_id : NULL,
      '#description' => $this->t('Specify a template which will be used to render the content.'),
    ];

    // Put the form element into the form's "advanced" group.
    $element['#group'] = 'advanced';

    return $element;
  }

}
