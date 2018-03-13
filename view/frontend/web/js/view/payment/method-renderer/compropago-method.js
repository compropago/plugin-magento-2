/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Compropago_Magento2/payment/compropagotpl',
                compropagoProvider: 'OXXO',
                providerSelector: "[id^=compropago_]:checked",
                provider: ""
            },

            initialize: function () {
                this._super()
                    .observe('compropagoProvider');
                return this;
            },

            getCode: function () {
                return 'compropago';
            },

            /** Returns providers json */
            getCompropagoProviders: function () {
                console.log('providers');
                return window.checkoutConfig.payment.compropago.compropagoProviders;
            },

            /*showLogos: function () {
                return window.checkoutConfig.payment.compropago.compropagoLogos == '1';
            },*/

            compropagoSelectedProvider: function (newvalue) {
                this.compropagoProvider(newvalue);
                return !!newvalue;
            },

            getData: function () {
                var self = this;

                self.compropagoProvider = $(self.providerSelector).val();

                console.log('==> Provider:' + self.compropagoProvider);

                return {
                    "method": this.item.method,
                    "additional_data": {
                        "provider" : self.compropagoProvider
                    }
                };
            }
        });
    }
);
