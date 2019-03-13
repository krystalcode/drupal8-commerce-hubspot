<?php

namespace Drupal\hubspot_commerce\Plugin\QueueWorker;

use Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
    SyncToServiceInterface $sync_to
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

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
      $container->get('commerce_hubspot.sync_to.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($entity) {
    $this->syncTo->sync($entity);
  }

}
