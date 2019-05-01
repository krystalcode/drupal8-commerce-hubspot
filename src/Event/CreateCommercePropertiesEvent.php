<?php

namespace Drupal\commerce_hubspot\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that gets dispatched when the eCommerce bridge is about to installed.
 *
 * Allows modules to define the eCommerce properties that should be created on
 * Hubspot.
 *
 * @package Drupal\commerce_hubspot\Event
 */
class CreateCommercePropertiesEvent extends Event {

  const EVENT_NAME = 'commerce_hubspot.entity_sync_to.create_commerce_properties';

  /**
   * An array defining which properties should be created for each entity.
   *
   * @var array
   *   https://developers.hubspot.com/docs/methods/ecomm-bridge/ecomm-bridge-overview
   */
  protected $commerceProperties;

  /**
   * Constructs the object.
   *
   * @param array $commerce_properties
   *   An array of commerce properties keyed on the entity.
   */
  public function __construct(array $commerce_properties) {
    $this->commerceProperties = $commerce_properties;
  }

  /**
   * Gets the commerce properties.
   *
   * @return array
   *   An array of commerce properties keyed on the entity.
   */
  public function getCommerceProperties() {
    return $this->commerceProperties;
  }

  /**
   * Sets the commerce properties.
   *
   * @param array $commerce_properties
   *   An array of commerce properties keyed on the entity.
   *
   * @return $this
   */
  public function setCommerceProperties(array $commerce_properties) {
    $this->commerceProperties = $commerce_properties;
    return $this;
  }

}
