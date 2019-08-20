<?php

namespace Drupal\commerce_hubspot\Plugin\QueueWorker;

use Drupal\commerce_hubspot\Event\PostSyncEvent;
use Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class EntitySyncTo.
 *
 * @QueueWorker(
 *   id = "commerce_hubspot_entity_sync_to",
 *   title = @Translation("Sync Drupal entities to HubSpot"),
 *   cron = {"time" = 60}
 * )
 */
class EntitySyncTo extends QueueWorkerBase implements ContainerFactoryPluginInterface {

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
   * The Commerce Hubspot sync to service.
   *
   * @var \Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface
   */
  protected $syncTo;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    SyncToServiceInterface $sync_to
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->syncTo = $sync_to;
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
      $container->get('commerce_hubspot.sync_to')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Reset the entity cache so we get all new data.
    $this
      ->entityTypeManager
      ->getStorage($data['entity_type'])
      ->resetCache([$data['entity_id']]);

    $entity = $this
      ->entityTypeManager
      ->getStorage($data['entity_type'])
      ->load($data['entity_id']);

    $remote_id = $this->syncTo->sync($entity);

    if (!$remote_id) {
      return;
    }

    // Now dispatch an event to allow other modules to determine what to do with
    // the result of the sync.
    $event = new PostSyncEvent($entity, $remote_id);
    $this->eventDispatcher->dispatch(PostSyncEvent::EVENT_NAME, $event);
  }

}
