<?php

namespace Drupal\commerce_hubspot\Event;

use stdClass;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that gets dispatched when an entity is synced from Hubspot.
 *
 * Allows modules to interact during the entity update process.
 *
 * @package Drupal\commerce_hubspot\Event
 */
class SyncFromEntityUpdateEvent extends Event {

  const EVENT_NAME = 'commerce_hubspot.entity_sync_from.entity_update';

  /**
   * The Hubspot entity that was updated.
   *
   * @var array
   */
  protected $hubspotEntity;

  /**
   * Constructs the SyncFromEntityUpdateEvent object.
   *
   * @param object $hubspot_entity
   *   The Hubspot entity that was updated.
   */
  public function __construct(stdClass $hubspot_entity) {
    $this->hubspotEntity = $hubspot_entity;
  }

  /**
   * Gets the Hubspot entity.
   *
   * @return stdClass
   *   The Hubspot entity.
   */
  public function getHubspotEntity() {
    return $this->hubspotEntity;
  }

}
