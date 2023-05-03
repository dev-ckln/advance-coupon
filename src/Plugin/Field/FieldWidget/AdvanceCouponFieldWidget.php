<?php

namespace Drupal\advance_coupon\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Plugin implementation of the 'advance_coupon_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "advance_coupon_field_widget",
 *   label = @Translation("Advance Coupon Field"),
 *   field_types = {
 *     "advance_coupon_field"
 *   }
 * )
 */
class AdvanceCouponFieldWidget extends WidgetBase {
  use StringTranslationTrait;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   * The plugin_id for the formatter.
   * @param mixed $plugin_definition
   * The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   * The definition of the field to which the formatter is associated.
   * @param array $settings
   * The formatter settings.
   * @param string $label
   * The formatter label display setting.
   * @param string $view_mode
   * The view mode.
   * @param array $third_party_settings
   * Any third party settings settings.
   */


  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Field\FieldDefinitionInterface
   */
  protected $fieldDefinition;
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new AdvanceCouponController object.
   *
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed[] $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param mixed[] $settings
   *   The field settings.
   * @param mixed[] $third_party_settings
   *   Any third-party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct($plugin_id, array $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'label_type' => 'placeholder',
      'field_inline' => 0,
    ] + parent::defaultSettings();
  }

  /**
   * Get label display types list.
   */
  public static function getFieldLabelDisplayTypesList() {
    return [
      'placeholder' => 'Placeholder',
      'label' => 'Label',
      'both' => 'Both',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $label_types = self::getFieldLabelDisplayTypesList();
    $element['label_type'] = [
      '#title' => $this->t('Field label type'),
      '#type' => 'select',
      '#options' => $label_types,
      '#default_value' => $this->getSetting('label_type'),
      '#weight' => 15,
    ];
    $element['field_inline'] = [
      '#title' => $this->t('Display fields as inline'),
      '#description' => $this->t('Display fields as display inline-flex'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('field_inline'),
      '#weight' => 15,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $fields_list = $this->getFieldSetting('fields');
    $label_types = self::getFieldLabelDisplayTypesList();
    $label_type = $this->getSetting('label_type');
    $field_inline = $this->getSetting('field_inline') ? 'Inline' : '';

    $text = "Fields : ";
    $count = 0;
    foreach ($fields_list as $field) {
      if ($count) {
        $text .= ", ";
      }
      $text .= $field['name'];
      $count++;
    }
    $summary[] = $this->t('Number of fields @count. (@list)', [
      '@count' => $this->getFieldSetting('fields_count'),
      '@list' => $text,
    ]);
    $summary[] = $this->t('Form label type : @type. @inline', [
      '@type' => $label_types[$label_type],
      '@inline' => $field_inline,
    ]);

    return $summary;
  }

  /**
   * Drupal\file\Plugin\Field\FieldWidget\FileWidget::formMultipleElements().
   *
   * Special handling for draggable multiple widgets and 'add more' button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $item_values = $items->getValue();
    $field_inline = $this->getSetting('field_inline');
    $elements = parent::formMultipleElements($items, $form, $form_state);

    // Add lib.
    if ($field_inline) {
      $elements['#attached']['library'][] = 'advance_coupon/inlinefields.style';
    }
    // kint($elements).
    foreach ($item_values as $key => $item_value) {
      $item_data = NULL;
      if (!empty($item_value['data'])) {
        $item_data = $item_value['data'];
      }
      elseif (is_array($item_value)) {
        $item_data = $item_value;
      }
      $ei = "$key";
      // Element Index.
      if ($item_data) {
        // Field wrap.
        $elements[$ei]['#type'] = 'details';
        $elements[$ei]['#open'] = TRUE;
        $elements[$ei]['#title'] = $this->fieldDefinition->getLabel();
        $elements[$ei]['#description'] = $this->fieldDefinition->getDescription();
        foreach ($item_data as $item_key => $item_v) {
          $ii = "$item_key";
          // Item Index.
          if (isset($elements[$ei][$ii])) {
            $type = $elements[$ei][$ii]['#type'] ?? NULL;
            $default_value = $item_v;
            if ($type === "entity_autocomplete") {
              try {
                $target_type = $elements[$ei][$ii]['#target_type'];
                $default_value = $this->entityTypeManager->getStorage($target_type)
                  ->load($item_v);
                ;
              }
              catch (\Exception $e) {
              }
            }
            $elements[$ei][$ii]['#default_value'] = $default_value;
          }
        }
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];
    $field_settings = $this->getFieldSettings();
    $fields_list = $field_settings['fields'];
    $index_field_pos = $field_settings['index_field_pos'];
    $label_type = $this->getSetting('label_type');
    $label_type_title = ($label_type == 'label' || $label_type == 'both');
    $label_type_plhol = ($label_type == 'placeholder' || $label_type == 'both');
    $count = 0;
    foreach ($fields_list as $key => $field) {
      // Add index field (Index field holder).
      if ($index_field_pos == $key) {
        $element['index'] = [];
      }
      $field_name = $field['name'];
      $field_type = $field['type'];
      $field_conf = $field['conf'];
      if (strstr($field_type, 'basicfield_') !== FALSE) {
        $element[$count] = [
          '#type' => 'textfield',
          '#maxlength' => 1024,
          '#default_value' => '',
        ];
        if ($field_type === 'basicfield_int') {
          $element[$count]['#type'] = 'number';
        }
        elseif ($field_type === 'basicfield_bool') {
          $element[$count]['#type'] = 'checkbox';
          $element[$count]['#title'] = $field_name;
        }
        elseif ($field_type === 'basicfield_list' || $field_type === 'basicfield_radios') {
          // Multiple list selection.
          if ($field_type === 'basicfield_radios') {
            $element[$count]['#type'] = 'radios';
          }
          elseif (FALSE || $field_type === 'basicfield_checks') {
            // Tempory disabled, because multiple values not handeleing .
            $element[$count]['#type'] = 'checkboxes';
          }
          else {
            $element[$count]['#type'] = 'select';
            $element[$count]['#empty_value'] = '';
          }
          // Get options list.
          $options_temp = explode("\n", $field_conf);
          $options = [];
          foreach ($options_temp as $items) {
            if (strstr($items, '|') === FALSE) {
              $options[$items] = $items;
            }
            else {
              $items_data = explode("|", $items);
              $options[$items_data[0]] = $items_data[1];
            }
          }
          $element[$count]['#options'] = $options;
        }
      }
      else {
        // @todo Test with all type of Entities.
        $element[$count] = [
          '#type' => 'entity_autocomplete',
          '#target_type' => $field_type,
          '#default_value' => '',
        ];
      }
      // Add title to tooltip text.
      $element[$count]['#attributes']['title'] = $field_name;
      // Set field title and/or placeholder.
      if ($label_type_title) {
        $element[$count]['#title'] = $field_name;
      }
      if ($label_type_plhol) {
        $element[$count]['#placeholder'] = $field_name;
      }
      $count++;
    }
    return $element;
  }

}
