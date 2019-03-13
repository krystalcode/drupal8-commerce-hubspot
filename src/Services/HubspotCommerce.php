<?php

namespace Drupal\hubspot_commerce\Services;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hubspot_api\Manager;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Exception;

/**
 * The HubspotCommerce service class.
 *
 * Contains functions to sync data with the Hubspot API.
 *
 * @package Drupal\hubspot_commerce
 */
class HubspotCommerce {

  /**
   * The Hubspot API Manager.
   *
   * @var \Drupal\hubspot_api\Manager
   */
  protected $hubspotManager;

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
    $this->hubspotManager = $hubspot_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get(HUBSPOT_COMMERCE_LOGGER_CHANNEL);

    // Initialize our Hubspot API client.
    try {
      $this->client = $this->hubspotManager->getHandler()->client;
    }
    catch (Exception $e) {
      $this->logger->error('Could not create a Hubspot API client. Error: @error', [
        '@error' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Handles the queue items and syncs them with the Hubspot API.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we're syncing (ie. user/order/product variation).
   */
  public function syncQueueItem(EntityInterface $entity) {
    // TODO: Depending on which entity we're trying to sync, trigger the
    // appropriate action.
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
