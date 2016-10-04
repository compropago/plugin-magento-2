/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Payment/js/view/payment/default'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'ComproPago_MgPayment/payment/cp_checkout',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.'
            },
            placeOrderHandler: null,
            validateHandler: null,

            /**
             * @param {Object} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Object} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return 'compropago_mgpayment';
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                return true;
            }
        });
    }
);

