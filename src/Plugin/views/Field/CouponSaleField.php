<?php

namespace Drupal\advance_coupon\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Custom sales field handler.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("coupon_sale")
 */
class CouponSaleField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // No query needed.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Get the coupon id from the field settings.
    $uid = $this->view->field['uid']->original_value;
    $markup_data = $this->view->field['field_marketer_coupon_percentage']->original_value;
    preg_match('/Coupon Id: (\d+)/', (string) $markup_data, $matches);
    $coupon_id = $matches[1];
    $order_count = 0;
    // Get the count of orders for the logged-in user and coupon id.
    if (isset($coupon_id) && !empty($coupon_id)) {
      $query = \Drupal::entityQuery('commerce_order')
        ->condition('coupons', $coupon_id)
        ->condition('uid', $uid);
      $order_count = $query->count()->execute();
    }
    return $order_count;
  }

}
