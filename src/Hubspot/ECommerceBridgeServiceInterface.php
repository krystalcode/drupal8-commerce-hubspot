<?php

namespace Drupal\commerce_hubspot\Hubspot;

/**
 * Interface for the ECommerceBridgeService class.
 *
 * @package Drupal\commerce_hubspot\Hubspot
 */
interface ECommerceBridgeServiceInterface {

  /**
   * Install and enable the Hubspot eCommerce settings.
   */
  public function installSettings();

}
