<?php

namespace Drupal\advance_coupon\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\StringFilter;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Filter by start and end date.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("marketer_coupon_filter")
 */
class MarketerCouponFilter extends StringFilter {

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
    $this->valueTitle = t('Filter by Coupon code');
    $this->definition['options callback'] = [$this, 'generateOptions'];
    $this->currentDisplay = $view->current_display;
  }

  /**
   * Helper function that generates the options.
   *
   * @return array
   *   An array of states and their ids.
   */
  public function generateOptions() {
    $coupons = \Drupal::entityTypeManager()
      ->getStorage('commerce_promotion_coupon')
      ->loadMultiple();
    $options = [];
    foreach ($coupons as $coupon) {
      $options[$coupon->getCode()] = $coupon->id();
    }
    return $options;
  }

  /**
   * Helper function that builds the query.
   */
  public function query() {
    if (!empty($this->value)) {
      $coupon_id = $this->generateOptions()[$this->value];
      $configuration = [
        'table' => 'user__field_marketer_coupon_percentage',
        'field' => 'field_marketer_coupon_percentage_index',
        'operator' => '=',
      ];

      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->query->addWhere('AND', 'user__field_marketer_coupon_percentage.field_marketer_coupon_percentage_index', $coupon_id, 'IN');
    }
  }

}
