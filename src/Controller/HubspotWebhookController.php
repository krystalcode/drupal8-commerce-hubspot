<?php

namespace Drupal\commerce_hubspot\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Queue\QueueInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HubspotWebhookController.
 *
 * Processes the webhook request received from Hubspot.
 */
class HubspotWebhookController extends ControllerBase {

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The queue object.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * Constructs a new HubspotWebhookController object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Queue\QueueInterface $queue
   *   The queue interface.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, QueueInterface $queue) {
    $this->logger = $logger_factory->get(COMMERCE_HUBSPOT_LOGGER_CHANNEL);
    $this->queue = $queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('queue')->get('commerce_hubspot_entity_sync_from')
    );
  }

  /**
   * Capture the payload from Hubspot.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A simple string and 200 response.
   */
  public function capture(Request $request) {
    $response = new Response();

    // Capture the payload.
    $payload = $request->getContent();

    // Check if it is empty.
    if (empty($payload)) {
      $message = $this->t('The payload from Hubspot was empty.');
      $this->logger->error($message);
      $response->setContent($message);

      return $response;
    }

    // Add the $payload to our sync_from queue.
    $this->queue->createItem($payload);
    $response->setContent('Success!');

    return $response;
  }

  /**
   * Access callback to confirm that this webhook is actually from Hubspot.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   AccessResult allowed or forbidden.
   */
  public function access(Request $request) {
    $hubspot_signature = $request->headers->get('X-HubSpot-Signature');

    // TODO: verify Hubspot signature.
    if ('app_secret_hash' == $hubspot_signature) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
