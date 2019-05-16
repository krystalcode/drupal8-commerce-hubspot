<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The SyncFromService class.
 *
 * Contains functions for synchronizing data from Hubspot to Drupal.
 *
 * @package Drupal\commerce_hubspot
 */
class SyncFromService implements SyncFromServiceInterface {

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
   * Constructs a new HubSpot sync from service instance.
   *
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
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    EventDispatcherInterface $event_dispatcher
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_factory->get(COMMERCE_HUBSPOT_LOGGER_CHANNEL);
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function sync(EntityInterface $entity, $subscription_type) {
    $this->entity = $entity;

    // TODO: The actual syncing of the data to Drupal.
  }

}
