<?php

namespace Drupal\commerce_hubspot_engagement\Hubspot;

use Drupal\commerce_hubspot\Hubspot\SyncFromServiceInterface;

/**
 * Interface for the SyncFromServiceEngagement class.
 *
 * @package Drupal\commerce_hubspot_engagement\Hubspot
 */
interface SyncFromServiceEngagementInterface extends SyncFromServiceInterface {

  /**
   * Fetches the recent updated engagements from Hubspot.
   *
   * @param string $last_fetch_time
   *   The last time this fetch was run.
   *
   * @param array
   *   An array of Hubspot engagements.
   */
  public function fetchUpdatedEngagements($last_fetch_time = NULL);

}
