<?php

namespace Drupal\template_whisperer\Controller;

use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\template_whisperer\Entity\TemplateWhispererSuggestionEntityInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\template_whisperer\TemplateWhispererSuggestionUsage;
use Drupal\Core\Routing\UrlGeneratorInterface;

/**
 * Listing of Template Whisperer suggestions.
 */
class AdminSuggestionController extends ControllerBase {

  /**
   * Retrieves the entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Template Whisperer Suggestion Usage.
   *
   * @var \Drupal\template_whisperer\TemplateWhispererSuggestionUsage
   */
  protected $twSuggestionUsage;

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The pager manager.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * Class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TemplateWhispererSuggestionUsage $tw_suggestion_usage, UrlGeneratorInterface $url_generator, PagerManagerInterface $pager_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->twSuggestionUsage = $tw_suggestion_usage;
    $this->urlGenerator      = $url_generator;
    $this->pagerManager      = $pager_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
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
