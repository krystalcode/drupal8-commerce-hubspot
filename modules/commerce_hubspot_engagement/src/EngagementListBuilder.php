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
    $header['label'] = $this->t('Label');
    $header['bundle'] = $this->t('Type');
    $header['author_link'] = $this->t('Author');
    $header['order_link'] = $this->t('Order');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_hubspot_engagement\Entity\Engagement $entity */
    $row['label'] = $entity->toLink($entity->getEntityType()->getLabel()->render() . ' ' . $entity->id());
    $row['bundle'] = $entity->bundle->entity->label();
    $row['author_link'] = $entity->getOwner()->toLink($entity->getOwner()->getAccountName());
    $row['order_link'] = $entity->getOrder()->toLink($entity->getOrderId());

    return $row + parent::buildRow($entity);
  }

}
