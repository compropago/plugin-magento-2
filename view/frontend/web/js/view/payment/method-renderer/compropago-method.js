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
                template: 'Compropago_Magento2/payment/compropagotpl'
            },

            /** Returns send check to info */
            showProviders: function() {
                return window.checkoutConfig.payment.compropago.compropagoProviders;
            	
            }
           
        });
    }
);
