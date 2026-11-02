<?php

namespace Drupal\template_whisperer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface;
use Drupal\template_whisperer\TemplateWhispererSuggestionUsage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Listing of Template Whisperer suggestions.
 */
final class AdminSuggestionController extends ControllerBase {

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\template_whisperer\TemplateWhispererSuggestionUsage $twSuggestionUsage
   *   Template Whisperer Suggestion Usage.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The url generator service.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pagerManager
   *   The pager manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    protected TemplateWhispererSuggestionUsage $twSuggestionUsage,
    protected UrlGeneratorInterface $urlGenerator,
    protected PagerManagerInterface $pagerManager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new self(
      // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('template_whisperer.suggestion.usage'),
      $container->get('url_generator'),
      $container->get('pager.manager')
    );
  }

  /**
   * The usage admin page for Template Whisperer suggestion.
   *
   * @param \Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface $template_whisperer_suggestion
   *   The given entity from URL.
   *
   * @return array
   *   The render array for the usage page.
   */
  public function usage(TemplateWhispererSuggestionEntityInterface $template_whisperer_suggestion) {
    $output = [];

    // Init the table.
    $output['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Entity'),
        $this->t('Entity type'),
        $this->t('Registering module'),
        $this->t('Count'),
      ],
      '#empty' => $this->t('This suggestion has not been currently used.'),
    ];

    $usages = $this->twSuggestionUsage->listUsage($template_whisperer_suggestion);
    $count = $this->twSuggestionUsage->countUsage($template_whisperer_suggestion);

    $this->pagerManager->createPager($count, 30);
    // Pager.
    $output[] = [
      '#type'     => 'pager',
      '#quantity' => '3',
    ];

    foreach ($usages as $i => $usage) {
      $entityStorage = $this->entityTypeManager->getStorage($usage->type);
      $entity = $entityStorage->load($usage->id);

      // Build the table content.
      $output['table'][$i] = [
        'entity'      => '',
        'entity_type' => ['#markup' => $usage->type],
        'module'      => ['#markup' => $usage->module],
        'count'       => ['#markup' => $usage->count],
      ];

      // Build the table empty state.
      if (!empty($entity)) {
        $output['table'][$i]['entity'] = ['#markup' => '<a target="_blank" href="' . $entity->toUrl()->toString() . '">' . $entity->getTitle() . '</a>'];
      }
    }

    return $output;
  }

}
