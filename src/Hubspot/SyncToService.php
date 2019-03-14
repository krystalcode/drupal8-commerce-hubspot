<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\commerce_hubspot\Event\EntityMappingEvent;
use Drupal\hubspot_api\Manager;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

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
  public $client;

  /**
   * Constructs a new HubSpot Commerce service instance.
   *
   * @param \Drupal\hubspot_api\Manager $hubspot_manager
   *   The Hubspot API Manager class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manger.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   *
   * @throws \Exception
   */
  public function __construct(
    Manager $hubspot_manager,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get(COMMERCE_HUBSPOT_LOGGER_CHANNEL);

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
    // Dispatch an event to allow modules to tell us which Hubspot entity and ID
    // to sync this Drupal entity with.
    $entity_mapping = [];
    $event = new EntityMappingEvent($entity_mapping);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch(EntityMappingEvent::EVENT_NAME, $event);

    if (!isset($entity_mapping['type']) && !isset($entity_mapping['id'])) {
      return;
    }
  }

  /**
   * Syncs the contact details with Hubspot.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The user entity.
   */
  protected function syncContact(EntityInterface $entity) {
    // TODO: Create the necessary properties from the entity object.

    // TODO: Check if a contact already exists in Hubspot, if so, we update.
  }

  /**
   * Syncs the order details with Hubspot.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The user entity.
   */
  protected function syncDeal(EntityInterface $entity) {
    // TODO: Create the necessary properties from the entity object.

    // TODO: Check if a deal already exists in Hubspot, if so, we update.
  }

  /**
   * Syncs the product variation details with Hubspot.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The user entity.
   */
  protected function syncProduct(EntityInterface $entity) {
    // TODO: Create the necessary properties from the entity object.

    // TODO: Check if a product already exists in Hubspot, if so, we update.
  }

  /**
   * Syncs the line item details with Hubspot.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The user entity.
   */
  protected function syncLineItem(EntityInterface $entity) {
    // TODO: Create the necessary properties from the entity object.

    // TODO: Check if a line item already exists in Hubspot, if so, we update.
  }

}
