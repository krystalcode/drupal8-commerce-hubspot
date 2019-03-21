<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for the SyncToService class.
 *
 * @package Drupal\commerce_hubspot\Hubspot
 */
interface SyncToServiceInterface {

  /**
   * Sync an entity with Hubspot.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being synced.
   */
  public function sync(EntityInterface $entity);

}
