<?php

namespace Drupal\commerce_hubspot_engagement\EventSubscriber;

use Drupal\commerce_hubspot\Event\SyncEntityTypesEvent;

use Drupal\Core\Entity\EntityTypeManagerInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityTypesSubscriber.
 *
 * Subscribes to the SyncEntityTypes event.
 *
 * @package Drupal\commerce_hubspot_engagement\EventSubscriber
 */
class EntityTypesSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EntityTypesSubscriber instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Exception
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SyncEntityTypesEvent::EVENT_NAME => ['onSyncEntityTypes'],
    ];
  }

  /**
   * Defines which Drupal entities should be synced to Hubspot.
   *
   * @param \Drupal\commerce_hubspot\Event\SyncEntityTypesEvent $event
   *   The sync entity type event object.
   */
  public function onSyncEntityTypes(SyncEntityTypesEvent $event) {
    // Add our commerce_hubspot_engagement_entity.
    $entities_to_sync = array_merge($event->getEntitiesToSync(), [
      'commerce_hubspot_engagement',
    ]);

    // Finally, set the entities to sync.
    $event->setEntitiesToSync($entities_to_sync);
  }

}
