<?php

namespace Drupal\commerce_hubspot_engagement\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

interface EngagementInterface extends ContentEntityInterface, EntityChangedInterface {

  const ENGAGEMENT_TYPE_NOTE = 'note';
  const ENGAGEMENT_TYPE_EMAIL = 'email';
  const ENGAGEMENT_TYPE_TASK = 'task';
  const ENGAGEMENT_TYPE_MEETING = 'meeting';
  const ENGAGEMENT_TYPE_CALL = 'call';

  /**
   * Gets the engagement name.
   *
   * @return string
   *   The engagement name.
   */
  public function getName();

  /**
   * Sets the engagement name.
   *
   * @param string $name
   *   The engagement name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the engagement type.
   *
   * @return string
   *   The engagement type.
   */
  public function getEngagementType();

  /**
   * Sets the engagement type.
   *
   * @return mixed
   */
  public function setEngagementType($type);

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
}
