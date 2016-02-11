/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Compropago_Payments/payment/compropagotpl'
            },

            /** Returns send check to info */
            getPublicKey: function() {
                //return window.checkoutConfig.payment.compropago.publicKey;
            	return 'placeholder';
            },

            getPayableTo : function(){
            	return 'placeholder';
            }
           
        });
    }
);
