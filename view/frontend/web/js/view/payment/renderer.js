/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'avarda_card',
                component: 'Avarda_Checkout/js/view/payment/method'
            },
            {
                type: 'avarda_directpayment',
                component: 'Avarda_Checkout/js/view/payment/method'
            },
            {
                type: 'avarda_invoice',
                component: 'Avarda_Checkout/js/view/payment/method'
            },
            {
                type: 'avarda_loan',
                component: 'Avarda_Checkout/js/view/payment/method'
            },
            {
                type: 'avarda_checkout',
                component: 'Avarda_Checkout/js/view/payment/method'
            },
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
