<?php

namespace Drupal\advance_coupon\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A custom Views field plugin that displays a "Remove" button.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("remove_button")
 */
class RemoveButtonField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // No query implementation is necessary.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    // Get the entity object from the result row.
    $entity = $this->getEntity($values);
    if (!$entity instanceof EntityInterface) {
      return '';
    }

    // Get the assigned coupons id from the user's profile field.
    $markup_data = $this->view->field['field_marketer_coupon_percentage']->original_value;
    preg_match('/Coupon Id: (\d+)/', (string) $markup_data, $matches);
    $coupon_id = $matches[1];

    // If the user has no assigned coupons, return an empty string.
    if (empty($coupon_id)) {
      return '';
    }

    // Build the URL for the "Remove" button.
    $url = Url::fromRoute('advance_coupon.remove_coupon', [
      'coupon_id' => $coupon_id,
      'user' => $entity->id(),
    ]);

    // Render the "Remove" button as HTML.
    return [
      '#type' => 'link',
      '#title' => $this->t('Remove'),
      '#url' => $url,
      '#attributes' => [
        'class' => ['button'],
      ],
    ];
  }

}
