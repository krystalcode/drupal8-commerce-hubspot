<?php

namespace Drupal\commerce_hubspot\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that gets dispatched when an entity is about to be synced.
 *
 * Allows modules to define which Drupal fields will be synced to which HubSpot
 * fields.
 *
 * @package Drupal\commerce_hubspot\Event
 */
class FieldMappingEvent extends Event {

  const EVENT_NAME = 'commerce_hubspot.entity_sync_to.field_mapping';

  /**
   * An array telling us which Drupal fields should map to which Hubspot fields.
   *
   * The array key is the Drupal field name while the id is the hubspot field
   * name.
   *
   * @var array
   *   IE. return [
   *     'field_first_name' => [
   *       'type' => 'string',
   *       'id' => 'first_name',
   *       'status' => TRUE,
   *     ],
   *   ];
   */
  protected $fieldMapping;

  /**
   * Constructs the object.
   *
   * @param array $field_mapping
   *   An array of Drupal field names with the Hubspot mapping information.
   */
  public function __construct(array $field_mapping) {
    $this->fieldMapping = $field_mapping;
  }

}
