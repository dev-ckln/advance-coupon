<?php

namespace Drupal\advance_coupon\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("profit")
 */
class ProfitField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $uid = $this->view->field['uid']->original_value;
    // Get the value of the coupon id field from the View.
    $marketer_data = $this->view->field['field_marketer_coupon_percentage']->original_value;
    preg_match('/Coupon Id: (\d+)/', (string) $marketer_data, $matches);
    $coupon_code = $matches[1];
    preg_match('/Percentage Value: (\d+)/', (string) $marketer_data, $matches);
    // Convert the matched number to an integer.
    $percentage_value = (int) $matches[1];
    // Initialize variables for tracking profit.
    $total_earn_profit = 0;
    // If a coupon code was found, query for orders that used it.
    if (isset($coupon_code) && !empty($coupon_code)) {
      $query = \Drupal::entityQuery('commerce_order')
        ->condition('uid', $uid)
        ->condition('coupons', $coupon_code);
      $order_ids = $query->execute();
      // For each order that used the coupon code, calculate the profit.
      foreach ($order_ids as $order_id) {
        $order = \Drupal::entityTypeManager()->getStorage('commerce_order')->load($order_id);
        $order_total = $order->getTotalPrice()->getNumber();
        $total_earn_profit += $order_total * $percentage_value / 100;
      }

    }
    return $total_earn_profit;
  }

}
