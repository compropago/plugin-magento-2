/**
 * Created by Arthur on 05/10/16.
 */
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default'
    ],
    function($, Component){
        'use strict';

        return Component.extend({
            defaults:{
                template: 'ComproPago_MgPayment/payment/cp-checkout'
            },

            getCode : function(){
                return 'compropago_mgpayment';
            },

            isActive: function(){
                return true;
            },

            validate: function(){
                return true;
            }
        });
    }
);