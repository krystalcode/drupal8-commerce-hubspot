<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\commerce_hubspot\Event\EntityMappingEvent;
use Drupal\commerce_hubspot\Event\FieldMappingEvent;
use Drupal\hubspot_api\Manager;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

use SevenShores\Hubspot\Resources\Contacts;
use SevenShores\Hubspot\Resources\Deals;
use SevenShores\Hubspot\Resources\Products;
use SevenShores\Hubspot\Resources\LineItems;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The SyncToService class.
 *
 * Contains functions for synchronizing data to the HubSpot API.
 *
 * @package Drupal\commerce_hubspot
 */
class SyncToService implements SyncToServiceInterface {

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
   */
  public function sync(EntityInterface $entity) {
    $this->entity = $entity;

    // Dispatch an event to allow modules to tell us which Hubspot entity and ID
    // to sync this Drupal entity with.
    $entity_mapping = [];
    $event = new EntityMappingEvent($entity_mapping);
    $this->eventDispatcher->dispatch(EntityMappingEvent::EVENT_NAME, $event);

    if (empty($entity_mapping['type'])) {
      return;
    }

    // Now, dispatch another event to allow modules to define which Drupal
    // fields will be synced to which HubSpot fields for this entity.
    $field_mapping = [];
    $event = new FieldMappingEvent($field_mapping);
    $this->eventDispatcher->dispatch(FieldMappingEvent::EVENT_NAME, $event);

    if (empty($field_mapping)) {
      return;
    }

    // Prepare the paylaod to send to Hubspot.
    $hubspot_field_properties = $this->preparePayload($field_mapping);
    if (empty($hubspot_field_properties)) {
      return;
    }

    // Now, do the actual syncing depending on the entity type.
    $function_name = 'sync' . $entity_mapping['type'];
    $this->$function_name($hubspot_field_properties, $entity_mapping['id']);
  }

  /**
   * Prepare the payload for syncing the properties.
   *
   * @param array $field_mapping
   *   The array of fields that should be mapped.
   *
   * @return mixed
   *   An array of Hubspot properties with their values.
   */
  protected function preparePayload(array $field_mapping) {
    $hubspot_field_properties = [];
    foreach ($field_mapping as $drupal_field_name => $hubspot_field) {
      if (!$hubspot_field['status']) {
        return;
      }

      $hubspot_field_properties[] = [
        'property' => $hubspot_field['id'],
        'value' => $hubspot_field['value'],
      ];
    }

    return $hubspot_field_properties;
  }

  /**
   * Syncs the contact details with Hubspot.
   *
   * @param array $hubspot_field_properties
   *   An array of Hubspot properties with their values.
   * @param int $hubspot_entity_id
   *   The Hubspot contact ID if the contact has already been synced to Hubspot.
   *
   * @throws \Exception
   */
  protected function syncContact(array $hubspot_field_properties, $hubspot_entity_id = NULL) {
    $contacts = new Contacts($this->client);

    // Create the contact if it hasn't been synced yet.
    if (!$hubspot_entity_id) {
      $response = $contacts->create($hubspot_field_properties);

      // If we were successful, save the Hubspot contact ID in the entity.
      if ($response->getStatusCode() == 200) {
        $body = $response->getBody();

        $this->entity->set('hubspot_contact_id', $body['vid'])->save();
      }
    }
    else {
      $contacts->update($hubspot_entity_id, $hubspot_field_properties);
    }
  }

  /**
   * Syncs the order details with Hubspot.
   *
   * @param array $hubspot_field_properties
   *   An array of Hubspot properties with their values.
   * @param int $hubspot_entity_id
   *   The Hubspot deal ID if the deal has already been synced to Hubspot.
   *
   * @throws \Exception
   */
  protected function syncDeal(array $hubspot_field_properties, $hubspot_entity_id = NULL) {
    $deals = new Deals($this->client);

    // Create the deal if it hasn't been synced yet.
    if (!$hubspot_entity_id) {
      $response = $deals->create($hubspot_field_properties);

      // If we were successful, save the Hubspot deal ID in the entity.
      if ($response->getStatusCode() == 200) {
        $body = $response->getBody();

        $this->entity->set('hubspot_deal_id', $body['dealId'])->save();
      }
    }
    else {
      $deals->update($hubspot_entity_id, $hubspot_field_properties);
    }
  }

  /**
   * Syncs the product variation details with Hubspot.
   *
   * @param array $hubspot_field_properties
   *   An array of Hubspot properties with their values.
   * @param int $hubspot_entity_id
   *   The Hubspot deal ID if the deal has already been synced to Hubspot.
   */
  protected function syncProduct(array $hubspot_field_properties, $hubspot_entity_id = NULL) {
    // TODO: Create the API on the SDK first.
    $products = new Products($this->client);

    // Create the product if it hasn't been synced yet.
    if (!$hubspot_entity_id) {
      $response = $products->create($hubspot_field_properties);

      // If we were successful, save the Hubspot product ID in the entity.
      if ($response->getStatusCode() == 200) {
        $body = $response->getBody();

        $this->entity->set('hubspot_product_id', $body['objectId'])->save();
      }
    }
    else {
      $products->update($hubspot_entity_id, $hubspot_field_properties);
    }
  }

  /**
   * Syncs the line item details with Hubspot.
   *
   * @param array $hubspot_field_properties
   *   An array of Hubspot properties with their values.
   * @param int $hubspot_entity_id
   *   The Hubspot deal ID if the deal has already been synced to Hubspot.
   */
  protected function syncLineItem(array $hubspot_field_properties, $hubspot_entity_id = NULL) {
    // TODO: Create the API on the SDK first.
    $line_items = new LineItems($this->client);

    // Create the line item if it hasn't been synced yet.
    if (!$hubspot_entity_id) {
      $response = $line_items->create($hubspot_field_properties);

      // If we were successful, save the Hubspot line item ID in the entity.
      if ($response->getStatusCode() == 200) {
        $body = $response->getBody();

        $this->entity->set('hubspot_line_item_id', $body['objectId'])->save();
      }
    }
    else {
      $line_items->update($hubspot_entity_id, $hubspot_field_properties);
    }
  }

}
