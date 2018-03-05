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
    });
})(Backbone, Drupal);
