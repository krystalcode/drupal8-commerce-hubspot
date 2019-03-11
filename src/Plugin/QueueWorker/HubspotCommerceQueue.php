<?php

namespace Drupal\hubspot_commerce\Plugin\QueueWorker;

use Drupal\hubspot_commerce\Services\HubspotCommerce;

use Drupal\Core\Annotation\QueueWorker;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HubspotCommerceQueue.
 *
 * @QueueWorker(
 *   id = "hubspot_commerce_queue",
 *   title = @Translation("Hubspot Commerce queue"),
 *   cron = {"time" = 60}
 * )
 */
class HubspotCommerceQueue extends QueueWorker implements ContainerFactoryPluginInterface {

  /**
   * The Hubspot Commerce service.
   *
   * @var \Drupal\hubspot_commerce\Services\HubspotCommerce
   */
  protected $service;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    HubspotCommerce $service,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->service = $service;
    $this->logger = $logger_factory->get(HUBSPOT_COMMERCE_LOGGER_CHANNEL);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('hubspot_commerce.service'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($entity) {
    try {
      $this->service->syncQueueItem($entity);
    }
    catch (\Exception $e) {
      $this->logger->error('Syncing of data with the Hubspot API was unsuccessful. Error: @error', [
        '@error' => $e->getMessage(),
      ]);
    }
  }

}
