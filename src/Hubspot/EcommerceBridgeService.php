<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\hubspot_api\Manager;

use SevenShores\Hubspot\Resources\EcommerceBridge;
use function var_export;

/**
 * Contains functions to install and enable the eCommerce settings on Hubspot.
 *
 * @package Drupal\commerce_hubspot\Hubspot
 */
class EcommerceBridgeService implements ECommerceBridgeServiceInterface {

  /**
   * The client.
   *
   * @var \SevenShores\Hubspot\Http\Client
   */
  protected $client;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new HubSpot Commerce service instance.
   *
   * @param \Drupal\hubspot_api\Manager $hubspot_manager
   *   The Hubspot API Manager class.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   *
   * @throws \Exception
   */
  public function __construct(
    Manager $hubspot_manager,
    LoggerChannelFactoryInterface $logger_factory,
    MessengerInterface $messenger
  ) {
    $this->logger = $logger_factory->get(COMMERCE_HUBSPOT_LOGGER_CHANNEL);
    $this->messenger = $messenger;

    // Initialize our Hubspot API client.
    $this->client = $hubspot_manager->getHandler()->client;
  }

  /**
   * {@inheritdoc}
   */
  public function installSettings() {
    // Let's install the Drupal to Hubspot eCommerce bridge.
    // First, check if it has already been installed, if not, install it.
    try {
      $ecommerce_bridge = new EcommerceBridge($this->client);
      $response = $ecommerce_bridge->checkInstall();

      if ($response->getStatusCode() != 200) {
        $this->messenger->addError(t('An error occurred while fetching the Hubspot eCommerce install status.'));
        return;
      }

      $data = $response->getData();
      // Install the bridge if not installed already.
      if (!$data->installCompleted) {
        $response = $ecommerce_bridge->install();
      }

      // Create the eCommerce property mappings if not already mapped.
      if ($data->ecommSettingsEnabled) {
        return;
      }

      $response = $ecommerce_bridge->upsertSettings([
        'enabled' => TRUE,
        'importOnInstall' => FALSE,
        'dealSyncSettings' => $this->getDealPropertyMappings(),
        'productSyncSettings' => $this->getProductPropertyMappings(),
        'lineItemSyncSettings' => $this->getLineItemPropertyMappings(),
        'contactSyncSettings' => $this->getContactPropertyMappings(),
      ]);

      // An error occurred while sending the mapping.
      if ($response->getStatusCode() != 200) {
        $this->messenger->addError(t('An error occurred while creating the eCommerce property mappings in Hubspot.'));
        return;
      }

      // Check the response to see if we've successfully enabled the bridge.
      $data = $response->getData();
      // The eCommerce bridge still has not enabled.
      if (!$data->enabled) {
        $this->messenger->addError(t('Could not enable the eCommerce bridge on Hubspot.'));
        return;
      }

      // All good.
      $this->messenger->addError(t('Successfully installed and enabled the Hubspot eCommerce bridge.'));
    }
    catch (Exception $e) {
      $this->logger->error(t('An error occurred while trying to install the Hubspot eCommerce bridge. The error was: @error', [
        '@error' => $e->getMessage(),
      ]));

      $this->messenger->addError(t('An error occurred while trying to install the Hubspot eCommerce bridge.'));
    }
  }

  /**
   * Set up mappings for the deal properties.
   *
   * @return array
   *   An array of properties.
   */
  protected function getDealPropertyMappings() {
    return [
      'properties' => [
        [
          'propertyName' => 'order_total',
          'dataType' => 'NUMBER',
          'targetHubspotProperty' => 'amount',
        ],
        [
          'propertyName' => 'customer_id',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'hs_assoc__contact_ids',
        ],
        [
          'propertyName' => 'order_created',
          'dataType' => 'DATETIME',
          'targetHubspotProperty' => 'closedate',
        ],
        [
          'propertyName' => 'stage',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'dealstage',
        ],
      ],
    ];
  }

  /**
   * Set up mappings for the product properties.
   *
   * @return array
   *   An array of properties.
   */
  protected function getProductPropertyMappings() {
    return [
      'properties' => [
        [
          'propertyName' => 'product_title',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'productname',
        ],
        [
          'propertyName' => 'product_image',
          'dataType' => 'AVATAR_IMAGE',
          'targetHubSpotProperty' => 'ip__ecomm_bridge__image_url',
        ],
      ],
    ];
  }

  /**
   * Set up mappings for the line item properties.
   *
   * @return array
   *   An array of properties.
   */
  protected function getLineItemPropertyMappings() {
    return [
      'properties' => [
        [
          'propertyName' => 'order_id',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'hs_assoc__deal_id',
        ],
        [
          'propertyName' => 'product_id',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'hs_assoc__product_id',
        ],
      ],
    ];
  }

  /**
   * Set up mappings for the contact properties.
   *
   * @return array
   *   An array of properties.
   */
  protected function getContactPropertyMappings() {
    return [
      'properties' => [
        [
          'propertyName' => 'given_name',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'firstname',
        ],
        [
          'propertyName' => 'family_name',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'lastname',
        ],
        [
          'propertyName' => 'email',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'email',
        ],
      ],
    ];
  }

}
