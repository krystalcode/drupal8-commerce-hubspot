<?php

namespace Drupal\commerce_hubspot\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that gets dispatched when the eCommerce bridge is about to enabled.
 *
 * Allows modules to define the eCommerce bridge settings including the property
 * mappings.
 *
 * @package Drupal\commerce_hubspot\Event
 */
class BuildCommerceBridgeEvent extends Event {

  const EVENT_NAME = 'commerce_hubspot.entity_sync_to.build_commerce_bridge';

  /**
   * An array defining which Hubspot entity this entity should be mapped to.
   *
   * @var array
   *   https://developers.hubspot.com/docs/methods/ecommerce/upsert-settings
   */
  protected $ecommerceBridgeSettings;

  /**
   * Constructs the object.
   *
   * @param array $ecommerce_bridge_settings
   *   The settings needed to enable the eCommerce bridge.
   */
  public function __construct(array $ecommerce_bridge_settings) {
    $this->ecommerceBridgeSettings = $ecommerce_bridge_settings;
  }

  /**
   * Gets the ecommerce bridge settings.
   *
   * @return array
   *   The settings needed to enable the eCommerce bridge.
   */
  public function getEcommerceBridgeSettings() {
    return $this->ecommerceBridgeSettings;
  }

  /**
   * Sets the ecommerce bridge settings.
   *
   * @param array $ecommerce_bridge_settings
   *   The settings needed to enable the eCommerce bridge.
   *
   * @return $this
   */
  public function setEcommerceBridgeSettings(array $ecommerce_bridge_settings) {
    $this->ecommerceBridgeSettings = $ecommerce_bridge_settings;
    return $this;
  }

}
