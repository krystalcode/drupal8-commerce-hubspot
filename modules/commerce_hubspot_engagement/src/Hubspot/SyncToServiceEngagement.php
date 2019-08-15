<?php

namespace Drupal\commerce_hubspot_engagement\Hubspot;

use Drupal\commerce_hubspot\Hubspot\SyncToService;
use Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface;

use Exception;
use SevenShores\Hubspot\Resources\Engagements;

/**
 * Class SyncToServiceEngagement.
 *
 * Override the SyncToService class to allow for syncing Engagements as well.
 *
 * @package Drupal\commerce_hubspot_engagement\Hubspot
 */
class SyncToServiceEngagement extends SyncToService implements SyncToServiceInterface {

  /**
   * {@inheritDoc}
   */
  protected function preparePayload(array $field_mapping, $entity_id = NULL) {
    // If we're syncing an engagement, the payload is ready as is.
    if ($this->entity->getEntityTypeId() === 'commerce_hubspot_engagement') {
      return $field_mapping;
    }

    return parent::preparePayload($field_mapping, $entity_id);
  }

  /**
   * Syncs the engagement details with Hubspot.
   *
   * @param array $hubspot_payload
   *   An array of Hubspot engagement details.
   *
   * @return bool|string
   *   The remote ID. False otherwise.
   *
   * @throws \Exception
   */
  protected function syncEngagement(array $hubspot_payload) {
    try {
      $engagements_api = new Engagements($this->client);

      // Create/Update depending on if the entity has already been synced.
      if ($this->entity->get('field_hubspot_remote_id')->getValue()) {
        $response = $engagements_api->update(
          $this->entity->get('field_hubspot_remote_id')->value,
          $hubspot_payload['engagement'],
          $hubspot_payload['metadata']
        );
      }
      // Else, if the engagement hasn't been synced w/ Hubspot yet.
      else {
        $response = $engagements_api->create(
          $hubspot_payload['engagement'],
          $hubspot_payload['associations'],
          $hubspot_payload['metadata'],
          $hubspot_payload['attachments']
        );
      }

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->engagement->id;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to sync an engagement to Hubspot. The payload is: @payload. The error was: @error', [
          '@payload' => var_export($hubspot_payload),
          '@error' => $e->getMessage(),
        ]
      ));
    }

    return FALSE;
  }

}
