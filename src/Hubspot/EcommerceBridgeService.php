<?php

namespace Drupal\commerce_hubspot\Hubspot;

use Drupal\commerce_hubspot\Event\BuildCommerceBridgeEvent;
use Drupal\hubspot_api\Manager;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

use SevenShores\Hubspot\Resources\EcommerceBridge;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Contains functions to install and enable the eCommerce settings on Hubspot.
 *
 * @package Drupal\commerce_hubspot\Hubspot
 */
class EcommerceBridgeService implements ECommerceBridgeServiceInterface {

  use StringTranslationTrait;

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
   * An event dispatcher instance.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The eCommerce bridge service.
   *
   * @var \SevenShores\Hubspot\Resources\EcommerceBridge
   */
  protected $eCommerceBridge;

  /**
   * Constructs a new HubSpot Commerce service instance.
   *
   * @param \Drupal\hubspot_api\Manager $hubspot_manager
   *   The Hubspot API Manager class.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   *
   * @throws \Exception
   */
  public function __construct(
    Manager $hubspot_manager,
    LoggerChannelFactoryInterface $logger_factory,
    MessengerInterface $messenger,
    EventDispatcherInterface $event_dispatcher
  ) {
    $this->logger = $logger_factory->get(COMMERCE_HUBSPOT_LOGGER_CHANNEL);
    $this->messenger = $messenger;
    $this->eventDispatcher = $event_dispatcher;

    // Initialize our Hubspot API client.
    $this->client = $hubspot_manager->getHandler()->client;

    // Initialize the EcommerceBridge.
    $this->eCommerceBridge = new EcommerceBridge($this->client);
  }

  /**
   * {@inheritdoc}
   */
  public function installBridge() {
    // Let's install and enable the Drupal to Hubspot eCommerce bridge.
    try {
      // Install the eCommerce bridge.
      $this->installEcommerceBridge();

      // TODO: Create the necessary Commerce fields that we need on Hubspot.
      // We first need to add the Product and LineItem resources in the SDK.

      // Enable the eCommerce bridge.
      $this->enableEcommerceBridge();

      // All good.
      $this->messenger->addMessage($this->t('Successfully installed and enabled the Hubspot eCommerce bridge.'));
    }
    catch (Exception $e) {
      $this->logger->error($this->t('An error occurred while trying to install the Hubspot eCommerce bridge. The error was: @error', [
        '@error' => $e->getMessage(),
      ]));

      $this->messenger->addError($this->t('An error occurred while trying to install the Hubspot eCommerce bridge.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function uninstallBridge() {
    // Let's uninstall and delete the eCommerce bridge settings on Hubspot.
    try {
      // Uninstall.
      $this->eCommerceBridge->uninstall();
      // Delete settings.
      $this->eCommerceBridge->deleteSettings();

      // All good.
      $this->messenger->addMessage($this->t('Successfully uninstalled and deleted the eCommerce bridge settings on Hubspot.'));
    }
    catch (Exception $e) {
      $this->logger->error($this->t('An error occurred while trying to install the Hubspot eCommerce bridge. The error was: @error', [
        '@error' => $e->getMessage(),
      ]));

      $this->messenger->addError($this->t('An error occurred while trying to install the Hubspot eCommerce bridge.'));
    }
  }

  /**
   * Install the eCommerce bridge.
   *
   * @throws \SevenShores\Hubspot\Exceptions\BadRequest
   */
  protected function installEcommerceBridge() {
    // First, check if it has already been installed, if not, install it.
    $response = $this->eCommerceBridge->checkInstall();

    if ($response->getStatusCode() != 200) {
      $this->messenger->addError($this->t('An error occurred while fetching the Hubspot eCommerce install status.'));
      return;
    }

    // Install the bridge if not installed already.
    $data = $response->getData();
    if ($data->installCompleted) {
      return;
    }
    $this->eCommerceBridge->install();
  }

  /**
   * Enable the eCommerce bridge.
   */
  protected function enableEcommerceBridge() {
    // Create the eCommerce property mappings which will enable the bridge.
    $settings = [
      'enabled' => TRUE,
      'importOnInstall' => TRUE,
      'dealSyncSettings' => $this->getDealPropertyMappings(),
      'productSyncSettings' => $this->getProductPropertyMappings(),
      'lineItemSyncSettings' => $this->getLineItemPropertyMappings(),
      'contactSyncSettings' => $this->getContactPropertyMappings(),
    ];
    // Dispatch an event to allow other modules to modify the settings.
    $event = new BuildCommerceBridgeEvent($settings);
    $this->eventDispatcher->dispatch(BuildCommerceBridgeEvent::EVENT_NAME, $event);

    // Now, make our request to enable the eCommerce bridge.
    $response = $this->eCommerceBridge->upsertSettings($settings);

    // An error occurred while enabling.
    if ($response->getStatusCode() != 200) {
      $this->messenger->addError($this->t('An error occurred while enabling the eCommerce bridge on Hubspot.'));
      return;
    }

    // Check the response to see if we've successfully enabled the bridge.
    $data = $response->getData();
    // The eCommerce bridge still has not enabled.
    if (!$data->enabled) {
      $this->messenger->addError($this->t('Could not enable the eCommerce bridge on Hubspot.'));
      return;
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
          'propertyName' => 'commerce_order.state',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'dealstage',
        ],
        [
          'propertyName' => 'commerce_order.type',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'dealtype',
        ],
        [
          'propertyName' => 'commerce_order.name',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'dealname',
        ],
        [
          'propertyName' => 'commerce_order.number',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'ip__ecomm_bridge__order_number',
        ],
        [
          'propertyName' => 'commerce_order.id',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'hs_object_id',
        ],
        [
          'propertyName' => 'commerce_order.total',
          'dataType' => 'NUMBER',
          'targetHubspotProperty' => 'amount',
        ],
        [
          'propertyName' => 'commerce_order.created',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'createdate',
        ],
        [
          'propertyName' => 'commerce_order.updated',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'closedate',
        ],
        [
          'propertyName' => 'commerce_order.owner.id',
          'dataType' => 'NUMBER',
          'targetHubspotProperty' => 'hubspot_owner_id	',
        ],
        [
          'propertyName' => 'commerce_order.customer.id',
          'dataType' => 'NUMBER',
          'targetHubspotProperty' => 'hs_assoc__contact_ids	',
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
        /*[
          'propertyName' => 'commerce_product_variation.id',
          'dataType' => 'NUMBER',
          'targetHubspotProperty' => 'hs_product_id',
        ],*/
        [
          'propertyName' => 'commerce_product_variation.title',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'name',
        ],
        [
          'propertyName' => 'commerce_product_variation.price',
          'dataType' => 'NUMBER',
          'targetHubspotProperty' => 'price',
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
          'propertyName' => 'commerce_order.field_hubspot_remote_id',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'hs_assoc__deal_id',
        ],
        [
          'propertyName' => 'commerce_product_variation.field_hubspot_remote_id',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'hs_assoc__product_id',
        ],
        [
          'propertyName' => 'commerce_order_item.quantity',
          'dataType' => 'NUMBER',
          'targetHubspotProperty' => 'quantity',
        ],
        [
          'propertyName' => 'commerce_order_item.total',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'price',
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
          'propertyName' => 'user.email',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'email',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.given_name',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'firstname',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.family_name',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'lastname',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.address_line1',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'address_1',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.address_line2',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'address_2',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.locality',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'city',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.administrative_area',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'state',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.postal_code',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'zip',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.country_code',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'country',
        ],
        [
          'propertyName' => 'commerce_order.billing_address.field_phone_number',
          'dataType' => 'STRING',
          'targetHubspotProperty' => 'phone',
        ],
      ],
    ];
  }

}
