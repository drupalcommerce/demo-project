(($, _, Drupal, drupalSettings) => {
  const cache = {};
  Drupal.cartFlyout = {
    offcanvas: null,
    offcanvasBackground: null,
    getTemplate(data) {
      const id = data.id;
      if (!cache.hasOwnProperty(id)) {
        cache[id] = {
          render: _.template(data.data)
        };
      }
      return cache[id];
    },
    createFlyout() {
      const cartOffCanvas = document.createElement('aside');
      cartOffCanvas.id = 'cart-offcanvas';
      cartOffCanvas.classList.add('cart-offcanvas');
      cartOffCanvas.classList.add('is-closed');
      // @todo Allow customizing left/right.
      cartOffCanvas.classList.add('cart-offcanvas--right');

      const cartOffCanvasBg = document.createElement('div');
      cartOffCanvasBg.id = 'cart-offcanvas-bg';
      cartOffCanvasBg.classList.add('cart-offcanvas-bg');
      cartOffCanvasBg.classList.add('is-closed');
      cartOffCanvasBg.onclick = Drupal.cartFlyout.flyoutOffcanvasToggle;

      document.body.appendChild(cartOffCanvas);
      document.body.appendChild(cartOffCanvasBg);

      Drupal.cartFlyout.offcanvas = cartOffCanvas;
      Drupal.cartFlyout.offcanvasBackground = cartOffCanvasBg;
    },
    flyoutOffcanvasToggle() {
      Drupal.cartFlyout.offcanvas.classList.toggle('is-open')
      Drupal.cartFlyout.offcanvas.classList.toggle('is-closed')
      Drupal.cartFlyout.offcanvasBackground.classList.toggle('is-open')
      Drupal.cartFlyout.offcanvasBackground.classList.toggle('is-closed')
    },
    fetchCarts(model) {
      // @todo will not work on IE11 w/o a polyfill.
      let data = fetch(Drupal.url(`cart?_format=json`), {
        // By default cookies are not passed, and we need the session cookie!
        credentials: 'include'
      });
      data.then((res) => {
        return res.json();
      }).then((json) => {
        let count = 0;
        for (let i in json) {
          count += json[i].order_items.length;
        }
        model.set('count', count);
        model.set('carts', json);
        model.trigger('cartsLoaded', this);
      });

    }
  };
  Drupal.behaviors.cartFlyout = {
    attach(context) {
      $(context).find('.cart-flyout').once('cart-block-render').each(function () {
        Drupal.cartFlyout.createFlyout();
        const model = new Drupal.cartFlyout.CartBlockModel(
          drupalSettings.cartFlyout
        );
        const view = new Drupal.cartFlyout.CartBlockView({
          el: this,
          model,
        });
        Drupal.cartFlyout.fetchCarts(model);
      });
    }
  };
})(jQuery, _, Drupal, drupalSettings);
