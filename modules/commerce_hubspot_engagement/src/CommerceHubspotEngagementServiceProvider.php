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

    $definition = $container->getDefinition('commerce_hubspot.sync_from');
    $definition->setClass('Drupal\commerce_hubspot_engagement\Hubspot\SyncFromServiceEngagement');
  }

}
