<?php

namespace Drupal\commerce_hubspot_engagement\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface EngagementInterface for the Engagement entity.
 *
 * @package Drupal\commerce_hubspot_engagement\Entity
 */
interface EngagementInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  const ENGAGEMENT_TYPE_NOTE = 'note';
  const ENGAGEMENT_TYPE_EMAIL = 'email';
  const ENGAGEMENT_TYPE_TASK = 'task';
  const ENGAGEMENT_TYPE_MEETING = 'meeting';
  const ENGAGEMENT_TYPE_CALL = 'call';

  /**
   * Gets the order entity that this engagement is attached to.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface
   *   The order entity.
   */
  public function getOrder();

  /**
   * Gets the order ID of the order that this engagement is attached to.
   *
   * @return int
   *   The order ID.
   */
  public function getOrderId();

  /**
   * Gets the engagement creation timestamp.
   *
   * @return int
   *   The engagement creation timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the engagement creation timestamp.
   *
   * @param int $timestamp
   *   The engagement creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the engagement changed timestamp.
   *
   * @return int
   *   The engagement changed timestamp.
   */
  public function getChangedTime();

  /**
   * Sets the engagement changed timestamp.
   *
   * @param int $timestamp
   *   The engagement changed timestamp.
   *
   * @return $this
   */
  public function setChangedTime($timestamp);
}
