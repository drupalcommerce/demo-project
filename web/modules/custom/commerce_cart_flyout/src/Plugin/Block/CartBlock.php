<?php

namespace Drupal\commerce_cart_js\Plugin\Block;

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
          'commerce_cart_flyout/cart',
        ],
      ],
      '#markup' => Markup::create('<div class="commerce_cart_flyout"></div>'),
    ];
  }

}
