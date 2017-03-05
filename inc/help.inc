<?php

/**
 * @file
 * Contains includes hook to generate help page.
 */

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * Implements hook_help().
 */
function template_whisperer_help($route_name, RouteMatchInterface $route_match) {
  switch ($route_name) {
    // Main module help for the template_whisperer module.
    case 'help.page.template_whisperer':
      $output = '<h2>' . t('About') . '</h2>';
      $output .= '<p>' . t('Provides a formalized way to declare and suggest page templates using "Template Whisperer" aka "TW" entity.') . '</p>';

      $output .= '<h3>' . t('Intended worflow') . '</h3>';
      $output .= '<p>' . t('The module uses is own field and his own entity to generate more suggestions for your selected nodes.') . '</p>';

      $output .= '<p>' . t('The best way of using Template Whisperer is as follows:') . '</p>';
      $output .= '<ol>';
      $output .= '<li>' . t('Create your own <a href=":add-form">Template Whisperer</a>, fill in the specific name and suggestion you need.', [':add-form' => Url::fromRoute('entity.template_whisperer.add_form')->toString()]) . '</li>';
      $output .= '<li>' . t('Add the field "Template Whisperer" field via the field settings for that entity or bundle type.') . '</li>';
      $output .= '<li>' . t('When you edit the content of that entity or bundle you should then see the new "Template Whisperer" section on the Advanced tabs.') . '</li>';
      $output .= '<li>' . t('Select the suggestion you want to add for that entity or bundle.') . '</li>';
      $output .= '<li>' . t('You can now have access to your own suggestion such [entity-type-id]--[entity-type]--<strong>list-news.html.twig</strong>.') . '</li>';

      return $output;

    default:
  }
}
