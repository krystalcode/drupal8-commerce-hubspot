<?php

namespace Drupal\commerce_hubspot\Hubspot;

use stdClass;

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
   *   An array of Hubspot contacts.
   */
  public function fetchUpdatedContacts($last_fetch_time = NULL);

  /**
   * Syncs a Hubspot entity with the appropriate Drupal entity.
   *
   * @param stdClass $hubspot_entity
   *   The Hubspot entity (ie. contact/deal).
   */
  public function sync(stdClass $hubspot_entity);

}
