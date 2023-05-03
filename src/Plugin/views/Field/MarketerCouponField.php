<?php

namespace Drupal\advance_coupon\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Marketer Coupon field handler.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("marketer_coupon")
 */
class MarketerCouponField extends FieldPluginBase {


  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->currentDisplay = $view->current_display;
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // First check whether the field should be hidden.
    $options['hide_alter_empty'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Get the coupon id from the field settings.
    $markup_data = $this->view->field['field_marketer_coupon_percentage']->original_value;
    if ($markup_data) {
      preg_match('/Coupon Id: (\d+)/', (string) $markup_data, $matches);
      $coupon_id = $matches[1];
      $coupon_code = '';
      $coupon = \Drupal::entityTypeManager()->getStorage('commerce_promotion_coupon')->load($coupon_id);

      if (isset($coupon) && !empty($coupon)) {
        $coupon_code = $coupon->getCode();
      }
    }

    return $coupon_code;
  }

}
