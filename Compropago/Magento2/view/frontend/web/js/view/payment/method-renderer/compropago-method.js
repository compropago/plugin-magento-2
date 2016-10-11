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
                compropagoProvider: 'OXXO',
                loadCompropago: 0
            },

            initObservable: function () {
                this._super().observe('compropagoProvider');
                return this;
            },

            getCode: function(){
                return 'compropago';
            },

          
            
            /** Returns providers json */
            getCompropagoProviders: function() {               
            	return window.checkoutConfig.payment.compropago.compropagoProviders;
            } ,
            
            showLogos: function(){
            	return window.checkoutConfig.payment.compropago.compropagoLogos == '1';
            },
            
            compropagoSelectedProvider: function(newvalue){
            	this.loadCompropago++;

                if(this.loadCompropago>11){
                    this.compropagoProvider( newvalue );
                }
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
            
            }
            
            
            /*validate: function() {
                var form = '#compropago-form';
                return $(form).validation() && $(form).validation('isValid');

            }*/
           
        });
    }
);
