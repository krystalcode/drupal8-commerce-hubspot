<?php

namespace Drupal\commerce_hubspot_engagement\Hubspot;

use Drupal\commerce_hubspot\Hubspot\SyncFromService;

use Exception;
use SevenShores\Hubspot\Resources\Engagements;

/**
 * Class SyncFromServiceEngagement.
 *
 * Extends the SyncFromService class to sync engagements.
 *
 * @package Drupal\commerce_hubspot_engagement\Hubspot
 */
class SyncFromServiceEngagement extends SyncFromService implements SyncFromServiceEngagementInterface {

  /**
   * {@inheritDoc}
   */
  public function fetchUpdatedEngagements($last_fetch_time = NULL) {
    $params = [];

    // Add a time offset if we have a last_fetch_time.
    if ($last_fetch_time) {
      $params['timeOffset'] = $last_fetch_time;
    }

    try {
      $engagements_api = new Engagements($this->client);
      $response = $engagements_api->recent($params);

      // If we were successful, return the array of engagements.
      $engagements = [];
      if ($response->getStatusCode() == 200) {
        // Organize the engagements into an array keyed by the id.
        foreach ($response->getData()->results as $engagement) {
          $engagements[$engagement->engagement->id] = $engagement;
        }

        return $engagements;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to get recently updated engagements from Hubspot. The error was: @error', [
            '@error' => $e->getMessage(),
          ]
        ));
    }

    return FALSE;
  }

}
