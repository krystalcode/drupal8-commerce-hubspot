<?php

namespace Drupal\commerce_hubspot\Event;

use Drupal\Core\Entity\EntityInterface;

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
   * The entity that's being updated.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * An array with the Drupal and Hubspot fields and their sync values.
   *
   * The array key is the Drupal field name while the id is the hubspot field
   * name.
   *
   * @var array
   *   IE. return [
   *     'properties' => [
   *       'field_first_name' => [
   *       'type' => 'string',
   *       'id' => 'first_name',
   *       'status' => TRUE,
   *       'value' => $entity->get('field_first_name')->getValue()[0]['value'],
   *       ],
   *     ],
   *   ];
   */
  protected $fieldMapping;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that's being updated.
   * @param array $field_mapping
   *   An array of Drupal field names with the Hubspot mapping information.
   */
  public function __construct(EntityInterface $entity, array $field_mapping) {
    $this->entity = $entity;
    $this->fieldMapping = $field_mapping;
  }

  /**
   * Gets the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Gets the field mapping array.
   *
   * @return array
   *   The field mapping array.
   */
  public function getFieldMapping() {
    return $this->fieldMapping;
  }

  /**
   * Sets the field mapping.
   *
   * @param array $field_mapping
   *   The field mapping array.
   *
   * @return $this
   */
  public function setFieldMapping(array $field_mapping) {
    $this->fieldMapping = $field_mapping;
    return $this;
  }

}
