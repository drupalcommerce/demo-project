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
          // @todo add CSS to target a data attribute to hide cursor and underline.
          if (this.model.getCount() > 0) {
            Drupal.cartFlyout.flyoutOffcanvasToggle();
          }
        },
        render() {
            const template = Drupal.cartFlyout.getTemplate({
                id: 'commerce_cart_flyout_block',
                data: this.model.attributes.templates.block,
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
         * @inheritdoc
         */
        render() {
          const template = Drupal.cartFlyout.getTemplate({
            id: 'commerce_cart_js_block_icon',
            data: this.model.attributes.templates.icon,
          });
          this.$el.html(template.render({
            icon: this.model.getIcon(),
          }));
        },
      });
})(Backbone, Drupal);
