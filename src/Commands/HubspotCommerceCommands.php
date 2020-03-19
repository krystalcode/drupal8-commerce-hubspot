<?php

namespace Drupal\commerce_hubspot\Commands;

use Drupal\commerce_hubspot\Hubspot\ECommerceBridgeServiceInterface;
use Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Class HubspotCommerceCommands
 * @package Drupal\commerce_hubspot\Commands
 */
class HubspotCommerceCommands extends DrushCommands {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entity_type_manager;

  /**
   * @var \Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface
   */
  protected $syncToService;

  /**
   * @var \Drupal\commerce_hubspot\Hubspot\ECommerceBridgeServiceInterface;
   */
  protected $ecommerceBridgeService;

  /**
   * SimplesitemapCommands constructor.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface $sync_to_service
   * @param \Drupal\commerce_hubspot\Hubspot\ECommerceBridgeServiceInterface $ecommerce_bridge_service
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, SyncToServiceInterface $sync_to_service, ECommerceBridgeServiceInterface $ecommerce_bridge_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->syncToService = $sync_to_service;
    $this->ecommerceBridgeService = $ecommerce_bridge_service;
  }

  /**
   * Installs or updates Hupsot Ecommerce Bridge.
   *
   * @command commerce-hubspot:upsert-bridge
   * @validate-module-enabled commerce_hubspot
   * @aliases ch:upsertb
   */
  public function upsertBridge() {
    $this->ecommerceBridgeService->installBridge();
    $this->logger()->info('Ecommerce Bridge was installed/updated successfully.');
  }

  /**
   * Sync an entity with the Hubspot API. (Useful for testing)
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $entity_id
   *   The entity id.
   *
   * @command commerce-hubspot:sync-to
   * @validate-module-enabled commerce_hubspot
   * @aliases ch:s
   */
  public function syncTo($entity_type, $entity_id) {
    $entity = $this->entityTypeManager
      ->getStorage($entity_type)
      ->load($entity_id);

    if ($entity == NULL) {

      $this->logger()->error('Unable to load entity. Please check your entity type and id.');
    }
    $remote_id = $this->syncToService->sync($entity);
    $this->logger()->info('Entity sync finished successfully.');
    if ($remote_id != NULL) {
      $this->logger()->info('Hubspot returns :remote_id', [':remote_id' => $remote_id]);
    }
  }

}
