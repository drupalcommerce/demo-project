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
    // @todo inject
    $registry = \Drupal::getContainer()->get('theme.registry')->get();
    $twig_theme_registry = \Drupal::getContainer()->get('twig.loader.theme_registry');

    // @todo move to helper method.
    $block_theme = $registry['commerce_cart_flyout_block'];
    $block_twig = $twig_theme_registry->getSourceContext($block_theme['template'] . '.html.twig');
    $icon_theme = $registry['commerce_cart_flyout_block_icon'];
    $icon_twig = $twig_theme_registry->getSourceContext($icon_theme['template'] . '.html.twig');

    $offcanvas_theme = $registry['commerce_cart_flyout_offcanvas'];
    $offcanvas_twig = $twig_theme_registry->getSourceContext($offcanvas_theme['template'] . '.html.twig');
    $offcanvas_contents_theme = $registry['commerce_cart_flyout_offcanvas_contents'];
    $offcanvas_contents_twig = $twig_theme_registry->getSourceContext($offcanvas_contents_theme['template'] . '.html.twig');
    $offcanvas_contents_items_theme = $registry['commerce_cart_flyout_offcanvas_contents_items'];
    $offcanvas_contents_items_twig = $twig_theme_registry->getSourceContext($offcanvas_contents_items_theme['template'] . '.html.twig');

    return [
      '#attached' => [
        'library' => [
          'commerce_cart_flyout/flyout',
        ],
        'drupalSettings' => [
          'cartFlyout' => [
            'templates' => [
              'icon' => $icon_twig->getCode(),
              'block' => $block_twig->getCode(),
              'offcanvas' => $offcanvas_twig->getCode(),
              'offcanvas_contents' => $offcanvas_contents_twig->getCode(),
              'offcanvas_contents_items' => $offcanvas_contents_items_twig->getCode(),
            ],
            'url' => Url::fromRoute('commerce_cart.page')->toString(),
            'icon' => file_create_url(drupal_get_path('module', 'commerce') . '/icons/ffffff/cart.png'),
          ],
        ],
      ],
      '#markup' => Markup::create('<div class="cart-flyout"></div>'),
    ];
  }

}
