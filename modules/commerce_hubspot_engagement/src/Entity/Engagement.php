<?php

namespace Drupal\commerce_hubspot_engagement\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Engagement entity.
 *
 * @ingroup engagement
 *
 * @ContentEntityType(
 *   id = "engagement",
 *   label = @Translation("Engagement"),
 *   base_table = "engagement",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class Engagement extends ContentEntityBase implements EngagementInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEngagementType() {
    return $this->get('engagement_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEngagementType($type) {
    $this->set('engagement_type', $type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * Returns the timestamp of the last entity change across all translations.
   *
   * @return int
   *   The timestamp of the last entity save operation across all
   *   translations.
   */
  public function getChangedTimeAcrossTranslations() {
    $changed = $this->getUntranslated()->getChangedTime();
    foreach ($this->getTranslationLanguages(FALSE) as $language) {
      $translation_changed = $this->getTranslation($language->getId())->getChangedTime();
      $changed = max($translation_changed, $changed);
    }
    return $changed;
  }

  /**
   * Gets the timestamp of the last entity change for the current translation.
   *
   * @return int
   *   The timestamp of the last entity save operation.
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * Sets the timestamp of the last entity change for the current translation.
   *
   * @param int $timestamp
   *   The timestamp of the last entity save operation.
   *
   * @return $this
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * Determines the schema for the base_table property defined above.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The engagement name.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    // The timestamp of when the engagement was created.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the engagement was created.'));

    // The timestamp of when the engagement was changed.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the engagement was last edited.'));

    // The engagement type.
    $fields['engagement_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Engagement type'))
      ->setSetting('allowed_values_function', ['\Drupal\commerce_hubspot_engagement\Entity\Engagement', 'getEngagementTypes'])
      ->setRequired(TRUE)
      ->setDefaultValue(self::COMPATIBLE_ANY)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ]);

    // The order backreference, populated by the postSave() function.
    $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order'))
      ->setDescription(t('The parent order.'))
      ->setSetting('target_type', 'commerce_order')
      ->setReadOnly(TRUE);

    return $fields;
  }

  /**
   * Gets the allowed values for the 'engagement_type' base field.
   *
   * @return array
   *   The allowed values.
   */
  public static function getEngagementTypes() {
    return [
      self::ENGAGEMENT_TYPE_NOTE => t('Note'),
      self::ENGAGEMENT_TYPE_EMAIL => t('Email'),
      self::ENGAGEMENT_TYPE_TASK => t('Task'),
      self::ENGAGEMENT_TYPE_MEETING => t('Meeting'),
      self::ENGAGEMENT_TYPE_CALL => t('Call'),
    ];
  }

}
