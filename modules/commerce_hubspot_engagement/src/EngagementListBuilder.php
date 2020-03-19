<?php

namespace Drupal\commerce_hubspot_engagement;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EngagementListBuilder.
 *
 * @package Drupal\commerce_hubspot_engagement
 */
class EngagementListBuilder extends EntityListBuilder {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new EngagementListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    RouteMatchInterface $route_match,
    RendererInterface $renderer
  ) {
    parent::__construct($entity_type, $storage);

    $this->routeMatch = $route_match;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(
    ContainerInterface $container,
    EntityTypeInterface $entity_type
  ) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('current_route_match'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(){
    $header['label'] = $this->t('ID');
    $header['bundle'] = $this->t('Type');
    $header['author_link'] = $this->t('Author');
    $header['order_links'] = $this->t('Associated Orders');
    $header['contact_links'] = $this->t('Associated Contacts');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $order_links = $this->getOrderLinks($entity);
    $contact_links = $this->getContactLinks($entity);
    /** @var \Drupal\commerce_hubspot_engagement\Entity\Engagement $entity */
    $row['label'] = $entity->toLink($entity->id());
    $row['bundle'] = $entity->bundle->entity->label();
    $row['author_link'] = $entity->getOwner()->toLink($entity->getOwner()->getAccountName());
    $row['associated_order_links'] = $this->renderer->render($order_links);
    $row['associated_contact_links'] = $this->renderer->render($contact_links);

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $order_id = $this->routeMatch->getParameter('commerce_order');
    $query = $this->getStorage()->getQuery()
      ->condition('order_id', $order_id)
      ->sort($this->entityType->getKey('id'));

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

  /**
   * Get the associated order links for this engagement.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The engagement entity.
   *
   * @return string
   *   A string of order links.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getOrderLinks(EntityInterface $entity) {
    $output = [];

    foreach ($entity->get('associated_orders')->referencedEntities() as $order) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $output[] = $order->toLink($order->id());
    }

    return $render = [
      '#theme' => 'item_list',
      '#items' => $output,
    ];
  }

  /**
   * Get the associated order links for this engagement.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The engagement entity.
   *
   * @return string
   *   A string of order links.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getContactLinks(EntityInterface $entity) {
    $output = [];

    foreach ($entity->get('associated_contacts')->referencedEntities() as $user) {
      /** @var \Drupal\user\Entity\User $user */
      $output[] = $user->toLink($user->getAccountName());
    }

    return $render = [
      '#theme' => 'item_list',
      '#items' => $output,
    ];
  }

}
