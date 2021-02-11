/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote',
], function (
    ko,
    Component,
    stepNavigator,
    quote
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Avarda_Checkout/avarda'
        },
        isVisible: ko.observable(true),
        currentTotals: {},

        initialize: function () {
            this._super();
        },

        isVirtual: function () {
           return quote.isVirtual();
        },

        navigate: function () {

        },

        navigateToNextStep: function () {
            stepNavigator.next();
        }
    });
});
