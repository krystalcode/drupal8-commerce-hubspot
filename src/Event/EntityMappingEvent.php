<?php

namespace Drupal\commerce_hubspot\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that gets dispatched when an entity is about to be synced.
 *
 * Allows modules to define which Hubspot entity and ID a Drupal entity
 * will be mapped to.
 *
 * @package Drupal\commerce_hubspot\Event
 */
class EntityMappingEvent extends Event {

  const EVENT_NAME = 'commerce_hubspot.entity_sync_to.entity_mapping';

  /**
   * An array defining which Hubspot entity this entity should be mapped to.
   *
   * @var array
   *   IE. return [
   *     'type' => 'Contact',
   *     'id' => 12,
   *   ];
   */
  protected $entityMapping;

  /**
   * Constructs the object.
   *
   * @param array $entity_mapping
   *   The Hubspot entity and ID that this Drupal entity should be mapped to.
   */
  public function __construct(array $entity_mapping) {
    $this->entityMapping = $entity_mapping;
  }

}
