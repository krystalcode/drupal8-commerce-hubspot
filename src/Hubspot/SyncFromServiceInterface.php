<?php

namespace Drupal\commerce_hubspot\Hubspot;

/**
 * Interface for the SyncFromService class.
 *
 * @package Drupal\commerce_hubspot\Hubspot
 */
interface SyncFromServiceInterface {

  /**
   * Fetches the recent updated contacts from Hubspot.
   *
   * @param string $last_fetch_time
   *   The last time this fetch was run.
   *
   * @param array
   *   An array of Hubspot contacts keyed on the contact email.
   */
  public function fetchUpdatedContacts($last_fetch_time = NULL);

  /**
   * Syncs a Hubspot entity with the appropriate Drupal entity.
   *
   * @param array $hubspot_entity
   *   The Hubspot entity (ie. contact/deal) details in an array.
   *   Ie. ['entity_type' => 'contact', 'entity' => $entity].
   */
  public function sync(array $hubspot_entity);

}
