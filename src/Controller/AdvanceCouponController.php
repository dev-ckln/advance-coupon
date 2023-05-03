<?php

namespace Drupal\advance_coupon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Remove a coupon from the user's profile.
 */
class AdvanceCouponController extends ControllerBase {
  use StringTranslationTrait;
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
      $container->get('messenger')
    );
  }

  /**
   * Removes a coupon from the user's profile.
   *
   * @param int $coupon_id
   *   The ID of the coupon to remove.
   * @param int $user
   *   The ID of the user whose cart the coupon is being removed from.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the assigned coupons page.
   */
  public function userRemoveCoupon($coupon_id, $user) {

    // Load the user entity.
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user_entity = $user_storage->load($user);
    $username = $user_entity->getAccountName();
    $coupon = $this->entityTypeManager()->getStorage('commerce_promotion_coupon')->load($coupon_id);
    $coupon_code = $coupon->getCode();
    // Get the list of coupons applied to the user's cart.
    $applied_coupons = $user_entity->get('field_marketer_coupon_percentage')->getValue();

    // Check if the coupon is applied to the user's cart.
    foreach ($applied_coupons as &$sub_array) {
      if (in_array($coupon_id, $sub_array)) {
        $index = array_search($coupon_id, $sub_array);
        unset($sub_array[$index]);
        unset($sub_array[$index + 1]);
      }
    }

    // Remove empty subarrays from the array.
    $applied_coupons = array_filter($applied_coupons);
    // Update the user's entity to reflect the removed coupon.
    $user_entity->set('field_marketer_coupon_percentage', $applied_coupons);
    $user_entity->save();

    // Display a success message after the coupon has been removed.
    $this->messenger()->addStatus($this->t('The (@coupon) has been removed from (@username).', [
      '@coupon' => $coupon_code,
      '@username' => $username,
    ]));

    // Redirect the user to the assigned coupons page.
    return $this->redirect('view.assigned_coupons_to_marketer.page_1');
  }

}
