<?php

namespace Drupal\commerce_hubspot\Event;

use Drupal\Core\Entity\EntityInterface;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that gets dispatched when an entity has finished syncing with Hubspot.
 *
 * @package Drupal\commerce_hubspot\Event
 */
class PostSyncEvent extends Event {

  const EVENT_NAME = 'commerce_hubspot.entity_sync_to.post_sync';

  /**
   * The entity that was updated.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The Hubspot remote ID that was returned after syncing.
   *
   * @var int
   */
  protected $remoteId;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that was updated.
   * @param int $remote_id
   *   The Hubspot remote ID that was returned after syncing.
   */
  public function __construct(EntityInterface $entity, $remote_id) {
    $this->entity = $entity;
    $this->remoteId = $remote_id;
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
   * Gets the Hubspot remote ID.
   *
   * @return int
   *   The remote ID.
   */
  public function getRemoteId() {
    return $this->remoteId;
  }

}
