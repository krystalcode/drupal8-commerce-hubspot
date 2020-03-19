<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\commerce_hubspot\Event\EntityMappingEvent;
use Drupal\commerce_hubspot\Event\FieldMappingEvent;
use Drupal\hubspot_api\Manager;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

use Exception;
use SevenShores\Hubspot\Resources\EcommerceBridge;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The SyncToService class.
 *
 * Contains functions for synchronizing data to the HubSpot API.
 *
 * @package Drupal\commerce_hubspot
 */
class SyncToService implements SyncToServiceInterface {

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
   * The entity that will be synced.
   *
   * @var \Drupal\core\Entity\EntityInterface
   */
  protected $entity;

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
   * Handles the queue items and syncs them with the Hubspot API.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we're syncing (ie. user/order/product variation).
   *
   * @return mixed
   *   The remote ID.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function sync(EntityInterface $entity) {
    // Reset the entity cache as we might have an outdated entity.
    $this
      ->entityTypeManager
      ->getStorage($entity->getEntityTypeId())->resetCache([$entity->id()]);
    $this->entity = $entity;

    // Dispatch an event to allow modules to tell us which Hubspot entity and ID
    // to sync this Drupal entity with.
    $event = new EntityMappingEvent($entity, []);
    $this->eventDispatcher->dispatch(EntityMappingEvent::EVENT_NAME, $event);
    $entity_mapping = $event->getEntityMapping();

    if (empty($entity_mapping['type'])) {
      return;
    }

    // Now, dispatch another event to allow modules to define which Drupal
    // fields will be synced to which HubSpot fields for this entity.
    $event = new FieldMappingEvent($entity, []);
    $this->eventDispatcher->dispatch(FieldMappingEvent::EVENT_NAME, $event);
    $field_mapping = $event->getFieldMapping();

    if (empty($field_mapping)) {
      return;
    }

    // Prepare the paylaod to send to Hubspot.
    $hubspot_payload = $this->preparePayload(
      $field_mapping,
      $entity_mapping['id']
    );
    if (empty($hubspot_payload)) {
      return;
    }

    // Now, do the actual syncing depending on the entity type.
    $function_name = 'sync' . $entity_mapping['type'];

    return $this->$function_name($hubspot_payload);
  }

  /**
   * Prepare the payload for syncing the properties.
   *
   * @param array $field_mapping
   *   The array of fields that should be mapped.
   * @param int $entity_id
   *   The user ID if the contact has already been synced to Hubspot.
   *
   * @return mixed
   *   An array of Hubspot associations and properties with their values.
   */
  protected function preparePayload(array $field_mapping, $entity_id = NULL) {
    $hubspot_field_properties = [];
    foreach ($field_mapping['properties'] as $drupal_field_name => $hubspot_field) {
      if (!$hubspot_field['status']) {
        return;
      }

      $hubspot_field_properties[$drupal_field_name] = $hubspot_field['value'];
    }

    // Construct the payload array.
    $hubspot_payload = [
      [
        'integratorObjectId' => $entity_id,
        'action' => 'UPSERT',
        'changeOccurredTimestamp' => REQUEST_TIME,
        'propertyNameToValues' => $hubspot_field_properties,
        'associations' => isset($field_mapping['associations']) ?: [],
      ],
    ];

    return $hubspot_payload;
  }

  /**
   * Syncs the contact details with Hubspot.
   *
   * @param array $hubspot_payload
   *   An array of Hubspot sync message properties.
   *
   * @return bool|string
   *   The remote ID. False otherwise.
   *
   * @throws \Exception
   */
  protected function syncContact(array $hubspot_payload) {
    try {
      $bridge = new EcommerceBridge($this->client);
      $response = $bridge->sendSyncMessages('CONTACT', $hubspot_payload);

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->vid;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to sync a contact to Hubspot. The payload is: @payload. The error was: @error', [
          '@payload' => var_export($hubspot_payload),
          '@error' => $e->getMessage(),
        ]
      ));
    }

    return FALSE;
  }

  /**
   * Syncs the order details with Hubspot.
   *
   * @param array $hubspot_payload
   *   An array of Hubspot sync message properties.
   *
   * @return bool|string
   *   The remote ID. False otherwise.
   *
   * @throws \Exception
   */
  protected function syncDeal(array $hubspot_payload) {
    try {
      $bridge = new EcommerceBridge($this->client);
      $response = $bridge->sendSyncMessages('DEAL', $hubspot_payload);

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->dealId;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to sync a deal to Hubspot. The payload is: @payload. The error was: @error', [
          '@payload' => var_export($hubspot_payload),
          '@error' => $e->getMessage(),
        ]
      ));
    }

    return FALSE;
  }

  /**
   * Syncs the product variation details with Hubspot.
   *
   * @param array $hubspot_payload
   *   An array of Hubspot sync message properties.
   *
   * @return bool|string
   *   The remote ID. False otherwise.
   *
   * @throws \Exception
   */
  protected function syncProduct(array $hubspot_payload) {
    try {
      $bridge = new EcommerceBridge($this->client);
      $response = $bridge->sendSyncMessages('PRODUCT', $hubspot_payload);

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->objectId;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to sync a product to Hubspot. The payload is: @payload. The error was: @error', [
          '@payload' => var_export($hubspot_payload),
          '@error' => $e->getMessage(),
        ]
      ));
    }

    return FALSE;
  }

  /**
   * Syncs the line item details with Hubspot.
   *
   * @param array $hubspot_payload
   *   An array of Hubspot sync message properties.
   *
   * @return bool|string
   *   The remote ID. False otherwise.
   *
   * @throws \Exception
   */
  protected function syncLineItem(array $hubspot_payload) {
    try {
      $bridge = new EcommerceBridge($this->client);
      $response = $bridge->sendSyncMessages('LINE_ITEM', $hubspot_payload);

      // If we were successful, return the remote ID.
      if ($response->getStatusCode() == 200) {
        return $response->getData()->objectId;
      }
    }
    catch (Exception $e) {
      $this->logger->error(
        $this->t('An error occurred while trying to sync a line item to Hubspot. The payload is: @payload. The error was: @error', [
          '@payload' => var_export($hubspot_payload),
          '@error' => $e->getMessage(),
        ]
      ));
    }

    return FALSE;
  }

}
