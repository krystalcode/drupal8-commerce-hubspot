<?php

namespace Drupal\commerce_hubspot_engagement\Hubspot;

use Drupal\commerce_hubspot\Hubspot\SyncToService;
use Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface;

use Exception;
use SevenShores\Hubspot\Resources\Engagements;
use SevenShores\Hubspot\Resources\Owners;

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
  protected function syncContact(array $hubspot_payload) {
    if ($this->entity->get('field_is_hubspot_owner')->value) {
      return $this->syncOwner($hubspot_payload);
    }

    return parent::syncContact($hubspot_payload);
  }

  /**
   * {@inheritDoc}
   */
  protected function preparePayload(array $field_mapping, $entity_id = NULL) {
    // If we're syncing an engagement, the payload is ready as is.
    if ($this->entity->getEntityTypeId() === 'commerce_hubspot_engagement') {
      return $field_mapping;
    }
    // Else if, we're syncing a user that's a Hubspot owner, bypass the
    // payload prepare.
    else if ($this->entity->getEntityTypeId() === 'user'
      && $this->entity->get('field_is_hubspot_owner')->value) {
      return $field_mapping;
    }

    return parent::preparePayload($field_mapping, $entity_id);
  }

  /**
   * Syncs the owner details with Hubspot.
   *
   * @param array $hubspot_payload
   *   An array of Hubspot owner details.
   *
   * @return bool|string
   *   The remote ID. False otherwise.
   *
   * @throws \Exception
   */
  protected function syncOwner(array $hubspot_payload) {
    try {
      $owners_api = new Owners($this->client);

      // Create/Update depending on if the entity has already been synced.
      if ($this->entity->get('field_hubspot_remote_id')->getValue()) {
        // Set the owner ID in the payload.
        $hubspot_payload['properties']['ownerId'] = $this->entity->get('field_hubspot_remote_id')->value;
        $hubspot_payload['properties']['portalId'] = 4191768;

        $response = $owners_api->update(
          $this->entity->get('field_hubspot_remote_id')->value,
          $hubspot_payload['properties']
        );
      }
      // Else, if the owner hasn't been synced w/ Hubspot yet.
      else {
        $response = $owners_api->create(
          $hubspot_payload['properties']
        );
      }

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->ownerId;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to sync an owner to Hubspot. The payload is: @payload. The error was: @error', [
            '@payload' => var_dump($hubspot_payload),
            '@error' => $e->getMessage(),
          ]
        ));
    }

    return FALSE;
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
          '@payload' => var_dump($hubspot_payload),
          '@error' => $e->getMessage(),
        ]
      ));
    }

    return FALSE;
  }

}
