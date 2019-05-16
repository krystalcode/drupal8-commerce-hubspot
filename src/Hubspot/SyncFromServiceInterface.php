<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for the SyncFromService class.
 *
 * @package Drupal\commerce_hubspot\Hubspot
 */
interface SyncFromServiceInterface {

  /**
   * Sync a Hubspot entity with Drupal.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being synced.
   * @param string $subscription_type
   *   The subscription type.
   */
  public function sync(EntityInterface $entity, $subscription_type);

}
