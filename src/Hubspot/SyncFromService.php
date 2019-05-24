<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\commerce_hubspot\Event\SyncFromEntityUpdateEvent;
use Drupal\hubspot_api\Manager;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

use Exception;
use SevenShores\Hubspot\Resources\Contacts;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The SyncFromService class.
 *
 * Contains functions for synchronizing data from the HubSpot API to Drupal.
 *
 * @package Drupal\commerce_hubspot
 */
class SyncFromService implements SyncFromServiceInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The client.
   *
   * @var \SevenShores\Hubspot\Http\Client
   */
  protected $client;

  /**
   * The entity from Hubspot.
   *
   * @var array
   */
  protected $hubspotEntity;

  /**
   * An event dispatcher instance.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a new HubSpot Commerce service instance.
   *
   * @param \Drupal\hubspot_api\Manager $hubspot_manager
   *   The Hubspot API Manager class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manger.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   *
   * @throws \Exception
   */
  public function __construct(
    Manager $hubspot_manager,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    EventDispatcherInterface $event_dispatcher
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get(COMMERCE_HUBSPOT_LOGGER_CHANNEL);
    $this->eventDispatcher = $event_dispatcher;

    // Initialize our Hubspot API client.
    $this->client = $hubspot_manager->getHandler()->client;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchUpdatedContacts($last_fetch_time = NULL) {
    $params = [];

    // Add a time offset if we have a last_fetch_time.
    if ($last_fetch_time) {
      $params['timeOffset'] = $last_fetch_time;
    }

    try {
      $contacts_api = new Contacts($this->client);
      $response = $contacts_api->recent($params);

      // If we were successful, return the array of contacts.
      $contact_vids = [];
      if ($response->getStatusCode() == 200) {
        $contacts = $response->getData()->contacts;

        foreach ($contacts as $contact) {
          $contact_vids[] = $contact->vid;
        }

        // As all the properties are not returned in the above API call,
        // run another API call to fetch all the properties for these contacts.
        return $this->getContactDetails($contacts_api, $contact_vids);
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to get recently updated contacts from Hubspot. The error was: @error', [
            '@error' => $e->getMessage(),
          ]
        ));
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function sync(stdClass $hubspot_entity) {
    $this->hubspotEntity = $hubspot_entity;

    // Dispatch an event to allow modules to update and save this entity.
    try {
      $event = new SyncFromEntityUpdateEvent($this->hubspotEntity);
      $this->eventDispatcher->dispatch(SyncFromEntityUpdateEvent::EVENT_NAME, $event);
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while syncing from Hubspot to Drupal. The hubspot entity is: @id. The error was: @error', [
            '@hubspot_entity' => $this->hubspotEntity->vid,
            '@error' => $e->getMessage(),
          ]
        ));
    }
  }

  /**
   * Runs batch API calls to fetch all the contact details for a set of vids.
   *
   * @param \SevenShores\Hubspot\Resources\Contacts $contacts_api
   *   The contacts API.
   * @param array $contact_vids
   *   An array of contact vids.
   *
   * @return array
   *   An array of contact details from Hubspot.
   */
  protected function getContactDetails(Contacts $contacts_api, array $contact_vids) {
    $updated_contacts = [];

    // Run another query to fetch all the properties for these contacts.
    // Do a batch API call, but, we can only do 100 records at a time, so do
    // them in batches.
    $contacts_chunked = array_chunk($contact_vids, 100, TRUE);

    // Query 100 at a time.
    foreach ($contacts_chunked as $chunk) {
      $response = $contacts_api->getBatchByIds($chunk);

      if ($response->getStatusCode() == 200) {
        foreach ($response->getData() as $data) {
          $updated_contacts[$data->properties->email->value] = $data;
        }
      }
    }

    return $updated_contacts;
  }

}
