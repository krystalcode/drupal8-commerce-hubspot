<?php

namespace Drupal\commerce_hubspot\Event;

use Drupal\Core\Entity\EntityInterface;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that gets dispatched when an entity is updated.
 *
 * Allows modules to define which Drupal entities should be synced to Hubspot.
 *
 * @package Drupal\commerce_hubspot\Event
 */
class SyncEntityTypesEvent extends Event {

  const EVENT_NAME = 'commerce_hubspot.entity_sync_to.entity_types';

  /**
   * The entity that's being updated.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The array of entity types to sync.
   *
   * @var array
   */
  protected $entitiesToSync;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that's being updated.
   * @param array $entities_to_sync
   *   The entity types to sync.
   */
  public function __construct(EntityInterface $entity, array $entities_to_sync) {
    $this->entitiesToSync = $entities_to_sync;
    $this->entity = $entity;
  }

}
