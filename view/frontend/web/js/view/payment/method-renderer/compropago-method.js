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
                providerSelector: "#compropago_cash_providers",
                compropagoProvider: 'SEVEN_ELEVEN',
                provider: ""
            },

            initialize: function () {
                this._super()
                    .observe('compropagoProvider');
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
                this.compropagoProvider = $(this.providerSelector).val();

                console.log('==> Provider:' + this.compropagoProvider);

                return {
                    "method": this.item.method,
                    "additional_data": {
                        "provider" : this.compropagoProvider
                    }
                };
            }
        });
    }
);
