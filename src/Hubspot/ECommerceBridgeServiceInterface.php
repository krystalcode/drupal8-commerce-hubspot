<?php

namespace Drupal\commerce_hubspot\Hubspot;

/**
 * Interface for the ECommerceBridgeService class.
 *
 * @package Drupal\commerce_hubspot\Hubspot
 */
interface ECommerceBridgeServiceInterface {

  /**
   * Install and enable the Hubspot eCommerce bridge.
   */
  public function installBridge();

  /**
   * Uninstall the Hubspot eCommerce bridge.
   */
  public function uninstallBridge();

}
