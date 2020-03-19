<?php

namespace Drupal\commerce_hubspot_engagement\Controller;

use Drupal\commerce_order\Entity\OrderInterface;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EngagementController.
 *
 * @package Drupal\commerce_hubspot_engagement\Controller
 */
class EngagementController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a EngagementController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Bypasses the engagements add page if only one engagement type is available.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $commerce_order
   *   The commerce order that this engagement should be attached to.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function addPage(OrderInterface $commerce_order) {
    $build = [
      '#theme' => 'entity_add_list',
      '#cache' => [
        'tags' => $this
          ->entityTypeManager()
          ->getDefinition('commerce_hubspot_engagement_type')
          ->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use commerce_hubspot_engagement_type types the user has access to.
    $commerce_hubspot_engagement_types = $this
      ->entityTypeManager()
      ->getStorage('commerce_hubspot_engagement_type')
      ->loadMultiple();
    foreach ($commerce_hubspot_engagement_types as $type) {
      $access = $this
        ->entityTypeManager()
        ->getAccessControlHandler('commerce_hubspot_engagement')
        ->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[] = [
          'type' => $type->id(),
          'label' => $type->label(),
          'description' => '',
          'add_link' => Link::createFromRoute(
            $type->label(),
            'entity.commerce_hubspot_engagement.add_form',
            [
              'commerce_order' => $commerce_order->id(),
              'commerce_hubspot_engagement_type' => $type->id()
          ]),
        ];
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the engagements/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect(
        'entity.commerce_hubspot_engagement.add_form',
        [
          'commerce_order' => $commerce_order->id(),
          'commerce_hubspot_engagement_type' => $type['type']
      ]);
    }

    $build['#bundles'] = $content;

    return $build;
  }

}
