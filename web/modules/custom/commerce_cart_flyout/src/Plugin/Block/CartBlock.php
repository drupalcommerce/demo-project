<?php

namespace Drupal\commerce_cart_flyout\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Provides a cart block.
 *
 * @Block(
 *   id = "commerce_cart_flyout",
 *   admin_label = @Translation("Cart Flyout"),
 *   category = @Translation("Commerce")
 * )
 */
class CartBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#attached' => [
        'library' => [
          'commerce_cart_flyout/flyout',
        ],
        'drupalSettings' => [
          'cartFlyout' => [
            'url' => Url::fromRoute('commerce_cart.page')->toString(),
            'icon' => file_create_url(drupal_get_path('module', 'commerce') . '/icons/ffffff/cart.png'),
          ],
        ],
      ],
      '#markup' => Markup::create('<div class="cart-flyout"></div>'),
    ];
  }

}
