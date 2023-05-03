<?php

namespace Drupal\advance_coupon\Feeds;

use Drupal\feeds\FieldTargetDefinition;

/**
 * Provides a field definition wrapped over a field definition.
 */
class ACFieldTargetDefinition extends FieldTargetDefinition {

  /**
   * {@inheritdoc}
   */
  public function getPropertyLabel($property) {
    if (!empty($value = $this->properties[$property]['label'])) {
      return $value;
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDescription($property) {
    if (!empty($value = $this->properties[$property]['description'])) {
      return $value;
    }
    return '';
  }

}
