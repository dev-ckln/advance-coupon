<?php

namespace Drupal\advance_coupon\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Plugin implementation of the 'advance_coupon_field' field type.
 *
 * @FieldType(
 *   id = "advance_coupon_field",
 *   label = @Translation("Advance Coupon Field"),
 *   description = @Translation("This field stores the ID of an image file as an integer value."),
 *   category = @Translation("General"),
 *   default_widget = "advance_coupon_field_widget",
 *   default_formatter = "advance_coupon_field_formatter"
 * )
 */
class AdvanceCouponFieldItem extends FieldItemBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'fields_count' => 2,
      'fields' => [
        '0' => [
          'name' => 'Key',
          'type' => 'basicfield_text',
          'conf' => '',
        ],
        '1' => [
          'name' => 'Value',
          'type' => 'basicfield_text',
          'conf' => '',
        ],
      ],
      'index_field_name' => 'Index Field',
      'index_field_hide' => 0,
      'index_field_pos' => 0,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = [
      'default_value' => [],
    ] + parent::defaultFieldSettings();
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'index' => [
          'description' => 'Coupon id as index field.',
          'type' => 'varchar',
          'length' => 255,
        ],
        'data' => [
          'description' => 'Percentage Data.',
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'index' => ['index'],
        'data' => ['data'],

      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Index field.
    $properties['index'] = DataDefinition::create('string')
      ->setLabel(t('Coupon Id'));
    // Data (Map) field.
    $properties['data'] = DataDefinition::create('string')
      ->setLabel(t('Perentage Value'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];
    $settings = $this->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getSettings();
    $settings_fields = $this->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getSettings();
    $fields_count = $settings_fields['fields_count'];

    $field_type_groups = [
      'basic' => "" . $this->t('Basic field'),
      'content' => "" . $this->t('Content Entity'),
      'configuration' => "" . $this->t('Configuration Entity'),
    ];
    // Field types.
    $field_type_options = [];
    $field_type_options['basic']['basicfield_text'] = 'Text';
    $field_type_options['basic']['basicfield_int'] = 'Integer';
    $field_type_options['basic']['basicfield_bool'] = 'Checkbox';
    $field_type_options['basic']['basicfield_list'] = 'Selections list';
    $field_type_options['basic']['basicfield_radios'] = 'Selections radio buttons';
    // Because multiple values are not handeling.
    $itmes_list = \Drupal::entityTypeManager()->getDefinitions();
    foreach ($itmes_list as $itme_name => $item_object) {
      $category = $item_object->getGroup();
      $field_type_options[$category][$itme_name] = $item_object->getLabel();
    }

    // Rearange option groups.
    foreach ($field_type_groups as $key => $name) {
      if (isset($field_type_options[$key]) && $name) {
        $field_type_options[$name] = $field_type_options[$key];
        unset($field_type_options[$key]);
      }
    }
    $element['fields'] = [
      '#type' => 'details',
      '#title' => 'Fields',
      '#open' => TRUE,
    ];
    for ($i = 0; $i < $fields_count; $i++) {
      $di = "$i";
      // Data index.
      $element['fields'][$di] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Field') . " - " . $i,
      ];
      $element['fields'][$di]['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Field Name'),
        '#required' => TRUE,
        '#default_value' => empty($settings['fields'][$di]['name']) ? 'Untitled' : $settings['fields'][$di]['name'],
      ];
      $element['fields'][$di]['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Field Type'),
        '#options' => $field_type_options,
        '#required' => TRUE,
        '#default_value' => empty($settings['fields'][$di]['type']) ? '' : $settings['fields'][$di]['type'],
      ];
      // Dynamic / Conditional field.
      $field_id = "edit-settings-fields-$di-type";
      // Ex : edit-settings-fields-1-type.
      $element['fields'][$di]['conf'] = [
        '#title' => $this->t('Field configuration'),
        '#description' => $this->t('Add values list, One per line.'),
        '#type' => 'textarea',
        '#default_value' => empty($settings['fields'][$di]['conf']) ? '' : $settings['fields'][$di]['conf'],
        '#states' => [
          'visible' => [
            [':input[id="' . $field_id . '"]' => ['value' => 'basicfield_list']],
            [':input[id="' . $field_id . '"]' => ['value' => 'basicfield_radios']],
          ],
        ],
      ];
    }
    return $element;
  }

  /**
   * Ajax method of storageSettingsForm.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A response object containing the updated form.
   */
  public function storageSettingsFormAjax(array &$form, FormStateInterface $form_state) {

    // $form['testfield']['#title'] = "YES";
    $form['fieldsset']['#type'] = "hidden";
    return $form['fieldsset'];
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // Get base form from FileItem.
    $element = parent::fieldSettingsForm($form, $form_state);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    // @todo Return random value according to the settings.
    $values = [
      'index' => "Random Index $random",
      '0' => "Key random $random",
      '1' => "Value random $random",
    ];
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $values = $this->getValue();
    $flg_data = FALSE;
    foreach ($values as $value) {
      if ($value) {
        $flg_data = TRUE;
        break;
      }
    }
    if ($flg_data) {
      $this->index = $values[0];
      $this->data = $values[1];
    }
    else {
      $this->data = NULL;
    }
    parent::preSave();
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    // Delete Value if empty.
    $flg_data = FALSE;
    foreach ($values as $value) {
      if ($value) {
        $flg_data = TRUE;
        break;
      }
    }
    if (!$flg_data) {
      $values = NULL;
    }
    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function getValue($field = NULL) {

    $value = parent::getValue();
    if (!empty($value['index']) && !empty($value['data'])) {
      $value = [$value['index'], $value['data']];
    }
    // Get selected field.
    if ($field) {
      if ($field == 'index') {
        // Return Index field.
        $value = $value['index'];
      }
      elseif ($field == 'data') {
        // Return data fields including index field.
        $value = $value['data'];
      }
      elseif (isset($value[$field])) {
        // Looking for a custom field.
        $value = $value[$field];
      }
      else {
        // Looking for undefined field.
        $value = NULL;
      }
    }
    return $value;
  }

}
