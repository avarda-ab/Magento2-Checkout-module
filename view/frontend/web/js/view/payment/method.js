/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
define(['Magento_Checkout/js/view/payment/default'],
    function (Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Avarda_Checkout/payment'
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
            redirect: function () {
                window.location = '/avarda/checkout?fromCheckout=1';
            }
        });
    }
);
