<?php

namespace Drupal\commerce_hubspot_engagement\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Commerce Hubspot Engagement Type entity.
 *
 * A configuration entity used to manage bundles for the Commerce Hubspot
 * Engagement entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_hubspot_engagement_type",
 *   label = @Translation("Engagement Type"),
 *   bundle_of = "commerce_hubspot_engagement",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_prefix = "commerce_hubspot_engagement_type",
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_hubspot_engagement\EngagementTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\commerce_hubspot_engagement\Form\EngagementTypeEntityForm",
 *       "add" = "Drupal\commerce_hubspot_engagement\Form\EngagementTypeEntityForm",
 *       "edit" = "Drupal\commerce_hubspot_engagement\Form\EngagementTypeEntityForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer commerce hubspot engagements",
 *   links = {
 *     "canonical" = "/admin/commerce/config/engagement_types/{commerce_hubspot_engagement_type}",
 *     "add-form" = "/admin/commerce/config/engagement_types/add",
 *     "edit-form" = "/admin/commerce/config/engagement_types/{commerce_hubspot_engagement_type}/edit",
 *     "delete-form" = "/admin/commerce/config/engagement_types/{commerce_hubspot_engagement_type}/delete",
 *     "collection" = "/admin/commerce/config/engagement_types",
 *   }
 * )
 */
class EngagementType extends ConfigEntityBundleBase {

}
