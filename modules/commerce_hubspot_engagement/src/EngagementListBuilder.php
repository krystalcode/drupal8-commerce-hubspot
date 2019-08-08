<?php

namespace Drupal\commerce_hubspot_engagement;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Class EngagementListBuilder.
 *
 * @package Drupal\commerce_hubspot_engagement
 */
class EngagementListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(){
    $header['id'] = $this->t('Engagement ID');
    $header['name'] = $this->t('Engagement Name');
    $header['bundle'] = $this->t('Engagement Type');
    $header['order_link'] = $this->t('Order');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_hubspot_engagement\Entity\Engagement $entity */

    $row['id'] = $entity->toLink($entity->id());
    $row['name'] = $entity->getEntityType()->getLabel()->render();
    $row['bundle'] = $entity->bundle->entity->label();
    $row['order_link'] = $entity->getOrder()->toLink($entity->getOrderId());

    return $row + parent::buildRow($entity);
  }

}
