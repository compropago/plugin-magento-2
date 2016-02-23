/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
	     'Magento_Checkout/js/view/payment/default',
	     'jquery',
	     "mage/validation"
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Compropago_Magento2/payment/compropagotpl',
                compropagoProvider: 'OXXO'
            },
            initObservable: function () {
                this._super()
                    .observe('compropagoProvider');
                return this;
            },

            /** Returns providers Logo Display */
            showProviders: function() {
            	// get provider output buffer
                return window.checkoutConfig.payment.compropago.compropagoProvidersDisplay;
            	
            	
            },
            
            /** Returns providers json */
            getCompropagoProviders: function() {
               return window.checkoutConfig.payment.compropago.compropagoProviders;
            } ,
            
           
             
             getData: function() {
            	console.log("po_number:"+this.compropagoProvider());
            	 return {
                     "method": this.item.method,
                     'po_number': this.compropagoProvider(),
                     "additional_data": null
                 };
            
            },
            
            /**
             * On Place Order Btn Submit 
             */
           /* validate: function() {
                var form = '#compropago-form';
                return $(form).validation() && $(form).validation('isValid');
              
            }*/
           
        });
    }
);
