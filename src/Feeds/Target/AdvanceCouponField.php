<?php

namespace Drupal\advance_coupon\Feeds\Target;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\feeds\Plugin\Type\Target\ConfigurableTargetInterface;
use Drupal\feeds\Plugin\Type\Target\FieldTargetBase;
use Drupal\multivaluefield\Feeds\ACFieldTargetDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a text field mapper.
 *
 * @FeedsTarget(
 *   id = "advance_coupon_field",
 *   field_types = {
 *     "advance_coupon_field"
 *   }
 * )
 */
class AdvanceCouponField extends FieldTargetBase implements ConfigurableTargetInterface, ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs a Text object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccountInterface $user) {
    $this->user = $user;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected static function prepareTarget(FieldDefinitionInterface $field_definition) {
    $definition = ACFieldTargetDefinition::createFromFieldDefinition($field_definition);

    // Use index field to map data json array.
    $definition->addProperty('data', 'Index field of the JSON array');
    // Mapping for other fields.
    $settings = $field_definition->getSettings();
    foreach ($settings['fields'] as $id => $field) {
      $definition->addProperty('data_' . $id, "$id / {$field['name']}", "ACField {$field['name']}");
    }
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareValue($delta, array &$values) {
    parent::prepareValue($delta, $values);
    // Map JSON field values.
    for ($idx = 0; $idx < 2; $idx++) {
      $data[$idx] = $values['data_' . $idx];
    }
    $values = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = parent::getSummary();
    return $summary;
  }

}
