<?php

namespace Drupal\commerce_hubspot_engagement\Hubspot;

use Drupal\commerce_hubspot\Hubspot\SyncToService;
use Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface;

use Exception;
use SevenShores\Hubspot\Resources\Contacts;
use SevenShores\Hubspot\Resources\Deals;
use SevenShores\Hubspot\Resources\EcommerceBridge;
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
  protected function preparePayload(array $field_mapping, $entity_id = NULL) {
    // If we're syncing an engagement, the payload is ready as is.
    if ($this->entity->getEntityTypeId() === 'commerce_hubspot_engagement') {
      return $field_mapping;
    }
    // Else if, we're syncing a user that's a Hubspot owner, bypass the
    // payload prepare.
    else {
      if ($this->entity->getEntityTypeId() === 'user'
        && $this->entity->hasField('field_is_hubspot_owner')
        && $this->entity->get('field_is_hubspot_owner')->value
      ) {
        return $field_mapping;
      }
    }

    return parent::preparePayload($field_mapping, $entity_id);
  }

  /**
   * {@inheritDoc}
   */
  protected function syncContact(array $hubspot_payload) {
    // Sync Hubspot owner users.
    if ($this->entity->hasField('field_is_hubspot_owner')
      && $this->entity->get('field_is_hubspot_owner')->value) {
      return $this->syncOwner($hubspot_payload);
    }

    // Sync contacts.
    try {
      $bridge = new EcommerceBridge($this->client);
      $response = $bridge->sendSyncMessages('CONTACT', $hubspot_payload);

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 204) {
        // The sync messages won't return back anything so we have to fetch the
        // recently modified contacts and fetch the specific remote ID for this
        // contact from the array as we need these to map the engagements.
        return $this->getUserRemoteId($hubspot_payload);
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t(
          'An error occurred while trying to sync a contact to Hubspot. The payload is: @payload. The error was: @error', [
            '@payload' => var_dump($hubspot_payload),
            '@error' => $e->getMessage(),
          ]
        )
      );
    }

    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  protected function syncDeal(array $hubspot_payload) {
    try {
      $bridge = new EcommerceBridge($this->client);
      $response = $bridge->sendSyncMessages('DEAL', $hubspot_payload);

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 204) {
        // The sync messages won't return back anything so we have to fetch the
        // recently modified deals and fetch the specific remote ID for this
        // deal from the array as we need these to map the engagements.
        return $this->getOrderRemoteId($hubspot_payload);
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t(
          'An error occurred while trying to sync a deal to Hubspot. The payload is: @payload. The error was: @error', [
            '@payload' => var_dump($hubspot_payload),
            '@error' => $e->getMessage(),
          ]
        )
      );
    }

    return FALSE;
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
        $hubspot_payload['properties']['ownerId'] = $this
          ->entity
          ->get('field_hubspot_remote_id')->value;
        // @TODO: Figure out what the portal ID is as it won't sync w/o it.
        $hubspot_payload['properties']['portalId'] = 5586439;

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
        $this->t(
          'An error occurred while trying to sync an owner to Hubspot. The payload is: @payload. The error was: @error', [
            '@payload' => var_dump($hubspot_payload),
            '@error' => $e->getMessage(),
          ]
        )
      );
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
      ksm($hubspot_payload);

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
      ksm($response);

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->engagement->id;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t(
          'An error occurred while trying to sync an engagement to Hubspot. The payload is: @payload. The error was: @error', [
            '@payload' => var_dump($hubspot_payload),
            '@error' => $e->getMessage(),
          ]
        )
      );
    }

    return FALSE;
  }

  /**
   * Fetches the remote ID for a user from Hubspot.
   *
   * @param array $hubspot_payload
   *   The recently synced contact payload that was sent to Hubspot.
   *
   * @return int
   *   The contact ID if the user was found.
   */
  protected function getUserRemoteId(array $hubspot_payload) {
    // Fetch the contact by email.
    try {
      $contacts_api = new Contacts($this->client);
      $response = $contacts_api->getByEmail(
        $hubspot_payload[0]['propertyNameToValues']['user.email']
      );

      // If we were successful, go through the results and fetch the deal ID
      // for our just modified order.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->vid;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t(
          'An error occurred while trying to fetch a contact by email. The error was: @error', [
            '@error' => $e->getMessage(),
          ]
        )
      );
    }

    return FALSE;
  }

  /**
   * Fetches the remote ID for an order from Hubspot.
   *
   * @param array $hubspot_payload
   *   The recently synced order payload that was sent to Hubspot.
   *
   * @return int
   *   The deal ID if the order was found.
   */
  protected function getOrderRemoteId(array $hubspot_payload) {
    // Fetch the most recently modified deals and find our recently synced
    // order.
    try {
      $since_last_5_minutes = strtotime('-5 minutes') * 1000;

      $deals_api = new Deals($this->client);
      $response = $deals_api->getRecentlyModified([$since_last_5_minutes]);

      // If we were successful, go through the results and fetch the deal ID
      // for our just modified order.
      if ($response->getStatusCode() == 200) {
        foreach ($response->getData()->results as $deal) {
          if ($deal->properties->order_id->value == $hubspot_payload[0]['propertyNameToValues']['commerce_order.id']) {
            return $deal->dealId;
          }
        }
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t(
          'An error occurred while trying to fetch recently modified deals. The error was: @error', [
            '@error' => $e->getMessage(),
          ]
        )
      );
    }

    return FALSE;
  }

}
