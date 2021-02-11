/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
var config = {
  config: {
    mixins: {
      'Magento_Checkout/js/view/summary/abstract-total': {
        'Avarda_Checkout/js/mixins/abstract-total-mixin': true
      }
    }
  }
};
