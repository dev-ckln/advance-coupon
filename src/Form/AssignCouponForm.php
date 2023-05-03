<?php

namespace Drupal\advance_coupon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Assign a coupon to the user's Marketer's Field.
 */
class AssignCouponForm extends FormBase {
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
   * Constructs a new Messenger object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger) {
    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger'),
     );
  }

  /**
   * Get Form ID.
   */
  public function getFormId() {
    return 'advance_coupon_assign_form';
  }

  /**
   * Build function.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Add the coupon and user select fields to the form.
    $form['coupon'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Coupon'),
      '#target_type' => 'commerce_promotion_coupon',
      '#required' => TRUE,
    ];
    $form['user'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Select a user'),
      '#target_type' => 'user',
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
      '#required' => TRUE,
    ];
    $form['percentage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Profit Percentage'),
      '#required' => TRUE,
    ];
    // Add a submit button to the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Assign Coupon'),
    ];

    return $form;
  }

  /**
   * Validate function.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get the selected coupon and user IDs.
    $coupon_id = $form_state->getValue('coupon');
    $user_id = $form_state->getValue('user');
    if (isset($coupon_id) && !empty($coupon_id)) {
      $query = $this->entityTypeManager->getStorage('user')->getQuery();
      $query->condition('status', 1)
        ->condition('field_marketer_coupon_percentage.index', $coupon_id, '=');
      // Filter by the coupon ID.
      $assigned_coupon = $query->execute();
      if ($assigned_coupon) {
        $users = $this->entityTypeManager->getStorage('user')->loadMultiple($assigned_coupon);
        foreach ($users as $user) {
          $user = $user->id();
          // Load the user entity.
          $user_entity = $this->entityTypeManager->getStorage('user')->load($user_id);
          $username = $user_entity->getAccountName();
        }
        $form_state->setErrorByName('coupon', $this->t('This coupon has already been assigned to @username.', ['@username' => $username]));
      }
    }
  }

  /**
   * Submit function.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the selected coupon and user IDs.
    $coupon_id = $form_state->getValue('coupon');
    $user_id = $form_state->getValue('user');
    $percent = $form_state->getValue('percentage');
    // Load the user entity using entity reference field value.
    $user_entity = $this->entityTypeManager->getStorage('user')->load($user_id);
    // Get the user's username.
    $username = $user_entity->getAccountName();
    // Get the coupon code.
    $coupon = $this->entityTypeManager->getStorage('commerce_promotion_coupon')->load($coupon_id);
    $coupon_code = $coupon->getCode();

    // Get the current list of assigned coupons.
    $assigned_percentage = $user_entity->get('field_marketer_coupon_percentage')->getValue();
    // Add the new coupon & percetnage to the list.
    $new_coupon = [
      $coupon_id, $percent,
    ];
    $assigned_percentage[] = $new_coupon;
    // Save the updated list to the user entity.
    $user_entity->set('field_marketer_coupon_percentage', $assigned_percentage);
    $user_entity->save();
    // Display a message to the user.
    $messenger = $this->messenger;
    $messenger->addMessage($this->t('The (@coupon) has been assigned to (@username).', [
      '@coupon' => $coupon_code,
      '@username' => $username,
    ]));
    // Redirect the user to the assigned coupons page.
    $form_state->setRedirect('view.assigned_coupons_to_marketer.page_1');
  }

}
