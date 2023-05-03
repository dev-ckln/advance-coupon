<?php

/**
 * @file
 * Defines advance_coupon module custom view field.
 */

/**
 * Implements hook_views_data().
 */
function advance_coupon_views_data() {

  // Set the group for the custom table to appear in.
  $data['views']['table']['group'] = t('Advance Coupon');
  // Add a join flag to the table.
  $data['views']['table']['join'] = [
    // #global is a special flag which allows a table to appear all the time.
    '#global' => [],
  ];
  // Add a custom field plugin for the coupon sale.
  $data['views']['coupon_sale'] = [
    'title' => t('Coupon Sale'),
    'help' => t('Coupon sale field plugin.'),
    'field' => [
      'id' => 'coupon_sale',
    ],
  ];
  // Add a custom field plugin for the remove button.
  $data['views']['remove_button'] = [
    'title' => t('Remove Button'),
    'help' => t('Remove button field plugin.'),
    'field' => [
      'id' => 'remove_button',
    ],
  ];
  // Add a custom field plugin for the profit.
  $data['views']['profit'] = [
    'title' => t('Profit'),
    'help' => t('Profit field plugin.'),
    'field' => [
      'id' => 'profit',
    ],
  ];
  // Add a custom field plugin for the coupon code.
  $data['views']['marketer_coupon'] = [
    'title' => t('Marketer Coupon'),
    'help' => t('Marketer Coupon field plugin.'),
    'field' => [
      'id' => 'marketer_coupon',
    ],
  ];
  $data['views']['marketer_coupon_filter'] = [
    'title' => t('Marketer Coupon - Filter'),
    'filter' => [
      'title' => t('Marketer Coupon - Filter'),
      'field' => 'marketer_coupon',
      'id' => 'marketer_coupon_filter',
    ],
  ];
  return $data;
}
