((Backbone, Drupal) => {
    Drupal.cartFlyout.CartBlockModel = Backbone.Model.extend(/** @lends Drupal.cartFlyout.CartBlockModel# */{
    /**
     * @type {object}
     *
     * @prop {string} icon
     * @prop {number} count
     * @prop {object} countText
     * @prop {string} url
     */
    defaults: /** @lends Drupal.commerceCart.CartBlockModel# */ {

        /**
         * @type {string}
         */
        icon: '',

        /**
         * @type {number}
         */
        count: 0,

        /**
         * @type {Array}
         */
        carts: [],

        /**
         * @type {Object}
         */
        countText: {
          singular: '@count item',
          plural: '@count items'
        },

        /**
         * @type {string}
         */
        url: '',

        /**
         * @type {Array}
         */
        links: [
          `<a href="${Drupal.url('cart')}">${Drupal.t('View cart')}</a>`
        ],
      },
      getUrl() {
        return this.get('url');
      },
      getIcon() {
        return this.get('icon');
      },
      getCount() {
        return this.get('count');
      },
      getCountPlural() {
        return this.get('countText').plural;
      },
      getCountSingular() {
        return this.get('countText').singular;
      },
      getLinks() {
        return this.get('links');
      },
      getCarts() {
        return this.get('carts');
      }
    });
})(Backbone, Drupal);
