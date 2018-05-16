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
                template: 'Compropago_Payments/payment/compropagotpl',
                providerSelector: "#compropago_cash_providers",
                provider: ""
            },

            initialize: function () {
                this._super();
                    //.observe('compropagoProvider');
                return this;
            },

            getCode: function () {
                return 'compropago_cash';
            },

            /** Returns providers json */
            getCompropagoProviders: function () {
                return window.checkoutConfig.payment.compropago_cash.providers;
            },

            compropagoSelectedProvider: function (newvalue) {
                console.log('selected_provider');
                this.compropagoProvider(newvalue);
                return !!newvalue;
            },

            getData: function () {
                var provider = $(this.providerSelector).val();

                console.log('==> Provider:' + provider);

                return {
                    "method": this.item.method,
                    "additional_data": {
                        "provider" : provider
                    }
                };
            }
        });
    }
);
