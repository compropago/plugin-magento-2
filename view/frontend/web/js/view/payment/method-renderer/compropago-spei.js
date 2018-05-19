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
                template: 'Compropago_Magento2/payment/compropago-spei',
                provider: ""
            },

            initialize: function () {
                this._super();
                return this;
            },

            getCode: function () {
                return 'compropago_spei';
            },

            getData: function () {
                console.log('==> Provider: SPEI');

                return {
                    "method": this.item.method,
                    "additional_data": {
                        "provider" : 'SPEI'
                    }
                };
            }
        });
    }
);
