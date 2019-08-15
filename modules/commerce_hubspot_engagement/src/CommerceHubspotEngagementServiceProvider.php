<?php

namespace Drupal\commerce_hubspot_engagement;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

class CommerceHubspotEngagementServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritDoc}
   */
  public function alter(ContainerBuilder $container) {
    parent::alter($container);

    $sync_from_definition = $container->getDefinition('commerce_hubspot.sync_from');
    $sync_from_definition->setClass('Drupal\commerce_hubspot_engagement\Hubspot\SyncFromServiceEngagement');

    $sync_to_definition = $container->getDefinition('commerce_hubspot.sync_to');
    $sync_to_definition->setClass('Drupal\commerce_hubspot_engagement\Hubspot\SyncToServiceEngagement');
  }

}
