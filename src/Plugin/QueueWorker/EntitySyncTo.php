<?php

namespace Drupal\commerce_hubspot\Plugin\QueueWorker;

use Drupal\commerce_hubspot\Hubspot\SyncToServiceInterface;

use Drupal\Core\Entity\EntityTypeManagerInterface;
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
   * The entity type manager.
   *
   * @var \Drupal\core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
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
    SyncToServiceInterface $sync_to
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('commerce_hubspot.sync_to.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $entity = $this
      ->entityTypeManager
      ->getStorage($data['entity_type'])
      ->load($data['entity_id']);

    $this->syncTo->sync($entity);
  }

}
