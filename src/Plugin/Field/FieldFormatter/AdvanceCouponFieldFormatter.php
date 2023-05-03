<?php

namespace Drupal\advance_coupon\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'advance_coupon_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "advance_coupon_field_formatter",
 *   label = @Translation("Advance Coupon Field Formatter"),
 *   field_types = {
 *     "advance_coupon_field"
 *   }
 * )
 */
class AdvanceCouponFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // Render field values as Unordered List.
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => [
          $this->t('Coupon Id: @coupon_id', ['@coupon_id' => $item->index]),
          $this->t('Percentage Value: @percentage', ['@percentage' => $item->data]),
        ],
      ];
    }
    return $elements;
  }

}
