<?php

namespace Drupal\commerce_hubspot_engagement\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the Engagement entity edit forms.
 *
 * @ingroup commerce_hubspot_engagement
 */
class EngagementForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Set the order ID for the engagement from the route.
    /** @var \Drupal\commerce_hubspot_engagement\Entity\EngagementInterface $engagement */
    $engagement = $this->entity;
    $order_id = $this->getRouteMatch()->getParameter('commerce_order');
    $engagement->set('order_id', $order_id);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    // Output a message depending on, if the engagement is new or not.
    $entity = $this->entity;
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Successfully created a new %label engagement.', [
          '%label' => $entity->bundle(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Successfully saved the %label engagement.', [
          '%label' => $entity->bundle(),
        ]));
    }
  }

}
