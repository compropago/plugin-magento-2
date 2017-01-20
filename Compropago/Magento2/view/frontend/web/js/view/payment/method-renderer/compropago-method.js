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
                compropagoProvider: 'OXXO'
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

            showLogos: function () {
                return window.checkoutConfig.payment.compropago.compropagoLogos == '1';
            },

            compropagoSelectedProvider: function (newvalue) {
                this.compropagoProvider(newvalue);
                return !!newvalue;
            },

            getData: function () {

                var elems = $("[id^=compropago_]");
                var selected = "";

                for (var x = 0; x < elems.length; x++) {
                    if ($(elems[x]).is(':checked')) {
                        console.log("Valido para el loop");
                        selected = $(elems[x]).attr('value');
                    }
                }

                if(selected == ""){
                    selected = this.compropagoProvider();
                }

                console.log('Otro Data:' + selected);

                document.cookie = "provider=" + selected;

                console.log('====>> Cookies: '+document.cookie);

                return {
                    "method": this.item.method,
                    'po_number': selected,
                    "additional_data": null
                };

            }

        });
    }
);
