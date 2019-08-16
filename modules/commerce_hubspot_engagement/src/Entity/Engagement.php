<?php

namespace Drupal\commerce_hubspot_engagement\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

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
 *     "bundle" = "bundle",
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
 *       "default" = "Drupal\commerce_hubspot_engagement\Form\EngagementForm",
 *       "add" = "Drupal\commerce_hubspot_engagement\Form\EngagementForm",
 *       "edit" = "Drupal\commerce_hubspot_engagement\Form\EngagementForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/orders/{commerce_order}/engagements/{commerce_hubspot_engagement}",
 *     "collection" = "/admin/commerce/orders/{commerce_order}/engagements",
 *     "add-form" = "/admin/commerce/orders/{commerce_order}/engagements/add/{commerce_hubspot_engagement_type}",
 *     "edit-form" = "/admin/commerce/orders/{commerce_order}/engagements/{commerce_hubspot_engagement}/edit",
 *     "delete-form" = "/admin/commerce/orders/{commerce_order}/engagements/{commerce_hubspot_engagement}/delete",
 *   },
 *   bundle_entity_type = "commerce_hubspot_engagement_type",
 *   field_ui_base_route = "entity.commerce_hubspot_engagement_type.edit_form",
 * )
 */
class Engagement extends ContentEntityBase implements EngagementInterface {

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    $uri_route_parameters['commerce_order'] = $this->getOrderId();
    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
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

    // The timestamp of when the engagement was created.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the engagement was created.'));

    // The timestamp of when the engagement was changed.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the engagement was last edited.'));

    // The hubspot remote ID populated when syncing.
    $fields['field_hubspot_remote_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hubspot Remote ID'))
      ->setDescription(t('The Hubspot remote ID.'))
      ->setReadOnly(TRUE);

    // The owner user of the engagement.
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the engagement author.'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\commerce_order\Entity\Order::getCurrentUserId')
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    // The date that the engagement was last updated on Hubspot.
    $fields['hubspot_lastmodifieddate'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Hubspot Last Modified Date'))
      ->setDescription(t('The timestamp of when the engagement was last modified on Hubspot.'));

    // File attachments uploaded for this engagement.
    $fields['attachments'] = BaseFieldDefinition::create('file')
      ->setLabel(t('File Attachments'))
      ->setDescription(t('Files attached to this engagement.'))
      ->setSettings([
        'uri_scheme' => 'private',
        'file_directory' => 'engagements',
        'file_extensions' => 'txt pdf doc docx xls xlsx ppt pptx gif png jpg jpeg',
      ])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'file_default',
        'weight' => 10,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'file_generic',
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The order backreference, populated by the postSave() function.
    $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order'))
      ->setDescription(t('The parent order.'))
      ->setSetting('target_type', 'commerce_order')
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    return $fields;
  }

}
