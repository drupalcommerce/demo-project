((Backbone, Drupal) => {
    Drupal.cartFlyout.CartBlockView = Backbone.View.extend(/** @lends Drupal.cartFlyout.CartBlockView# */{
        initialize() {
            this.listenTo(this.model, 'cartsLoaded', this.render);
        },
        events: {
          'click .cart-block--link__expand': 'offcanvasOpen'
        },
        offcanvasOpen(event) {
          event.preventDefault();
          Drupal.cartFlyout.flyoutOffcanvasToggle();
        },
        render() {
            const template = Drupal.cartFlyout.getTemplate({
                id: 'commerce_cart_flyout_block',
                data: '<div class="cart--cart-block">\n' +
                '  <div class="cart-block--summary">\n' +
                '    <a class="cart-block--link__expand" href="<%= url %>">\n' +
                '      <span class="cart-block--summary__icon" />\n' +
                '      <span class="cart-block--summary__count"><%= count_text %></span>\n' +
                '    </a>\n' +
                '  </div>\n' +
                '</div>\n',
              });
              this.$el.html(template.render({
                url: this.model.getUrl(),
                count_text: Drupal.formatPlural(
                  this.model.getCount(),
                  this.model.getCountSingular(),
                  this.model.getCountPlural(),
                )
              }));
              const icon = new Drupal.cartFlyout.CartIconView({
                el: this.$el.find('.cart-block--summary__icon'),
                model: this.model
              });
              icon.render();
            // Rerun any Drupal behaviors.
            Drupal.attachBehaviors();
        }
    });
    Drupal.cartFlyout.CartIconView = Backbone.View.extend(/** @lends Drupal.cartFlyout.CartIconView# */{
        /**
         * Adjusts the body element with the toolbar position and dimension changes.
         *
         * @constructs
         *
         * @augments Backbone.View
         */
        initialize() { },

        /**
         * @inheritdoc
         */
        render() {

          const template = Drupal.cartFlyout.getTemplate({
            id: 'commerce_cart_js_block_icon',
            data: '<img src="<%= icon %>" alt="Cart"/>',
          });
          this.$el.html(template.render({
            icon: this.model.getIcon(),
          }));
        },
      });
})(Backbone, Drupal);
