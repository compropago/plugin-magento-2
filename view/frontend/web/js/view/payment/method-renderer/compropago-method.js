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
	     "mage/validation",
	     'ko'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Compropago_Magento2/payment/compropagotpl',
                //default value
                compropagoProvider: 'OXXO'
            },

            initObservable: function () {

                console.log("initObservable");

                this._super()
                    .observe('compropagoProvider');
                this._super()
                .observe('');
                
                return this;
            },

          
            
            /** Returns providers json */
            getCompropagoProviders: function() {               
            	return window.checkoutConfig.payment.compropago.compropagoProviders;
            } ,
            
            /** Returns providers Logo Display */
            showProviders: function() {
            	// get provider output buffer
                return window.checkoutConfig.payment.compropago.compropagoProvidersDisplay;
            },
            
            showLogos: function(){
            	
            	if( window.checkoutConfig.payment.compropago.compropagoLogos == '1'){
            		return true;
            	}
            	return false;
            },
            compropagoSelectedProvider: function(){
            	ko.observable("OXXO");
            },
           
             getData: function() {
                 console.log('getData:'+this.compropagoProvider());

                 document.cookie = "provider = "+this.compropagoProvider();
                 document.cookie = "payment_method = compropago";

            	 return {
                     "method": this.item.method,
                     'po_number': this.compropagoProvider(),
                     "additional_data": null
                 };
            
            },
            
            /**
             * On Place Order Btn Submit 
             */
           /*validate: function() {
                var form = '#compropago-form';
                return $(form).validation() && $(form).validation('isValid');
              
            }*/
           
        });
    }
);
