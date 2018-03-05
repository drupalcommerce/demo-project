((Backbone, Drupal) => {
    Drupal.cartFlyout.CartOffcanvasView = Backbone.View.extend(/** @lends Drupal.cartFlyout.CartOffcanvasView# */{
        initialize() {
            this.listenTo(this.model, 'cartsLoaded', this.render);
        },
        events: {
          'click .cart-block--offcanvas-cart-table__remove button': 'removeItem',
        },
        removeItem(e) {
          e.preventDefault();
          const target = JSON.parse(e.target.value);
          const endpoint = Drupal.url(`cart/${target[0]}/items/${target[1]}?_format=json`);
          fetch(endpoint, {
            // By default cookies are not passed, and we need the session cookie!
            credentials: 'include',
            method: 'delete'
          })
            .then((res) => {})
            .finally(() => Drupal.cartFlyout.fetchCarts());
        },
        /**
         * @inheritdoc
         */
        render() {

          // @todo create a new View, or move `cart--cart-offcanvas`
          // This would allow us to use Twig since we do not need condiitonals.
          const template = Drupal.cartFlyout.getTemplate({
            id: 'commerce_cart_flyout_offcanvas',
            data: Drupal.cartFlyout.templates.offcanvas,
          });
          this.$el.html(template.render({
            count: this.model.getCount(),
            links: this.model.getLinks(),
          }));
          const contents = new Drupal.cartFlyout.CartContentsView({
            el: this.$el.find('.cart-block--offcanvas-contents__items'),
            model: this.model
          });
          contents.render();
        },
      });
      Drupal.cartFlyout.CartContentsView = Backbone.View.extend(/** @lends Drupal.cartFlyout.CartContentsView# */{
        /**
         * @inheritdoc
         */
        render() {

          const template = Drupal.cartFlyout.getTemplate({
            id: 'commerce_cart_flyout_offcanvas_contents',
            data: Drupal.cartFlyout.templates.offcanvas_contents
          });
          this.$el.html(template.render({
            carts: this.model.getCarts(),
          }));

          // @todo Cart model and Collection.
          this.$el.find('[data-cart-contents]').each(function () {
            let contents = new Drupal.cartFlyout.CartContentsItemsView({
              el: this,
              model: Drupal.cartFlyout.model
            });
            contents.render();
          });
        },
      });
      Drupal.cartFlyout.CartContentsItemsView = Backbone.View.extend(/** @lends Drupal.cartFlyout.CartContentsItemsView# */{
        cart: {},
        initialize() {
          this.cart = this.$el.data('cart-contents');
        },
        events: {
          'change .cart-block--offcanvas-cart-table__quantity input[type="number"]': 'onQuantityChange',
          'click .cart-block--offcanvas-contents__update': 'updateCart'
        },
        onQuantityChange(e) {
          const targetDelta = e.target.dataset.key;
          const value = e.target.value;
          this.cart.order_items[targetDelta].quantity = parseInt(value);
        },
        updateCart(event) {
          event.preventDefault();
          const endpoint = Drupal.url(`cart/${this.cart.order_id}/items?_format=json`);
          fetch(endpoint, {
            // By default cookies are not passed, and we need the session cookie!
            credentials: 'include',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            // Shout PATCH, see https://github.com/github/fetch/issues/254
            method: 'PATCH',
            body: JSON.stringify( this.cart.order_items )
          })
            .then((res) => {})
            .finally(() => Drupal.cartFlyout.fetchCarts());
        },
        /**
         * @inheritdoc
         */
        render() {
          const template = Drupal.cartFlyout.getTemplate({
            id: 'commerce_cart_flyout_offcanvas_contents_items',
            data:
            '        <table class="cart-block--offcanvas-cart-table table">' +
            '         <tbody>\n' +
            '        <% _.each(cart.order_items, function(orderItem, key) { %>' +
            '            <tr>\n' +
            '              <td class="cart-block--offcanvas-cart-table__title"><%- orderItem.title %></td>\n' +
            '              <td class="cart-block--offcanvas-cart-table__quantity">' +
            '                <input type="number" data-key="<% print(key) %>" value="<% print(parseInt(orderItem.quantity)) %>" style="width: 35px" />' +
            '              </td>\n' +
            '              <td class="cart-block--offcanvas-cart-table__price"><%= orderItem.total_price.formatted %></td>\n' +
            '              <td class="cart-block--offcanvas-cart-table__remove"><button value="<% print(JSON.stringify([cart.order_id, orderItem.order_item_id]))  %>" class="button btn">x</button></td>' +
            '            </tr>\n' +
            '        <% }); %>' +
            '          </tbody>\n' +
            '          <tfoot>' +
            '<td/>' +
            '<td colspan="3"><button type="submit" class="cart-block--offcanvas-contents__update button btn btn-primary">Update quantities</button></td>' +
            '          </tfoot>' +
            '        </table>\n'
          });
          this.$el.html(template.render({
            cart: this.cart
          }));
        },
      });
})(Backbone, Drupal);
