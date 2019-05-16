<?php

namespace Drupal\commerce_hubspot\Plugin\QueueWorker;

use Drupal\commerce_hubspot\Hubspot\SyncFromServiceInterface;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class EntitySyncFrom.
 *
 * @QueueWorker(
 *   id = "commerce_hubspot_entity_sync_from",
 *   title = @Translation("Sync HubSpot entities with Drupal"),
 *   cron = {"time" = 60}
 * )
 */
class EntitySyncFrom extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * An event dispatcher instance.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The Commerce Hubspot sync from service.
   *
   * @var \Drupal\commerce_hubspot\Hubspot\SyncFromServiceInterface
   */
  protected $syncFrom;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    SyncFromServiceInterface $sync_from
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->syncFrom = $sync_from;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('commerce_hubspot.sync_from')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Decode the JSON that was captured from the Hubspot request.
    $decoded_data = Json::decode($data);

    // Load the entity that was updated.
    $entity_id = $decoded_data['objectId'];
    $entity = $this
      ->entityTypeManager
      ->getStorage($data['entity_type'])
      ->load($data['entity_id']);

    $this->syncFrom->sync($entity, $decoded_data['subscriptionType']);
  }

}
