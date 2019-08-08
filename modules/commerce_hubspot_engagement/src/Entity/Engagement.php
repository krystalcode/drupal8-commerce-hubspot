<?php

namespace Drupal\commerce_hubspot_engagement\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Commerce Hubspot Engagement entity.
 *
 * @ingroup commerce_hubspot_engagement
 *
 * @ContentEntityType(
 *   id = "commerce_hubspot_engagement",
 *   label = @Translation("Engagement"),
 *   base_table = "commerce_hubspot_engagement",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   fieldable = TRUE,
 *   admin_permission = "administer commerce hubspot engagements",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_hubspot_engagement\EngagementListBuilder",
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/engagements/{commerce_hubspot_engagement}",
 *     "add-page" = "/admin/commerce/engagements/add",
 *     "add-form" = "/admin/commerce/engagements/add/{commerce_hubspot_engagement_type}",
 *     "edit-form" = "/admin/commerce/engagements/{commerce_hubspot_engagement}/edit",
 *     "delete-form" = "/admin/commerce/engagements/{commerce_hubspot_engagement}/delete",
 *     "delete-multiple-form" = "/admin/commerce/engagements/delete",
 *     "collection" = "/admin/commerce/engagements"
 *   },
 *   bundle_entity_type = "commerce_hubspot_engagement_type",
 *   field_ui_base_route = "entity.commerce_hubspot_engagement_type.edit_form",
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
  public function getOrder() {
    return $this->get('order_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderId() {
    return $this->get('order_id')->target_id;
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
      ->setDefaultValue(self::ENGAGEMENT_TYPE_NOTE)
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
