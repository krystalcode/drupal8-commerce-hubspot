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
   * The Hubspot entity type that was updated.
   *
   * @var array
   */
  protected $hubspotEntityType;

  /**
   * Constructs the SyncFromEntityUpdateEvent object.
   *
   * @param array $hubspot_entity
   *   The Hubspot entity that was updated.
   */
  public function __construct(array $hubspot_entity) {
    $this->hubspotEntityType = $hubspot_entity['entity_type'];
    $this->hubspotEntity = $hubspot_entity['entity'];
  }

  /**
   * Gets the Hubspot entity type.
   *
   * @return string
   *   The Hubspot entity type.
   */
  public function getHubspotEntityType() {
    return $this->hubspotEntityType;
  }

  /**
   * Gets the Hubspot entity.
   *
   * @return array|stdClass
   *   The Hubspot entity.
   */
  public function getHubspotEntity() {
    return $this->hubspotEntity;
  }

}
