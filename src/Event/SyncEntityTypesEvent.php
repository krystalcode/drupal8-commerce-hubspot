<?php

namespace Drupal\commerce_hubspot\Event;

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
   * The array of entity types to sync.
   *
   * @var array
   */
  protected $entitiesToSync;

  /**
   * Constructs the object.
   *
   * @param array $entities_to_sync
   *   The entity types to sync.
   */
  public function __construct(array $entities_to_sync) {
    $this->entitiesToSync = $entities_to_sync;
  }

}
