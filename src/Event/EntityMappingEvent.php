<?php

namespace Drupal\commerce_hubspot\Event;

use Drupal\Core\Entity\EntityInterface;

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
   * The entity that's being updated.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that's being updated.
   * @param array $entity_mapping
   *   The Hubspot entity and ID that this Drupal entity should be mapped to.
   */
  public function __construct(EntityInterface $entity, array $entity_mapping) {
    $this->entity = $entity;
    $this->entityMapping = $entity_mapping;
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
   * Gets the entity mapping array.
   *
   * @return array
   *   The entity mapping array.
   */
  public function getEntityMapping() {
    return $this->entityMapping;
  }

}
