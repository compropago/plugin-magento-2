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
                compropagoProvider: 'OXXO'
            },

            initObservable: function () {

                console.log("initObservable");

                this._super()
                    .observe('compropagoProvider');
                return this;
            },

          
            
            /** Returns providers json */
            getCompropagoProviders: function() {

                console.log('getCompropagoProviders');

            	var providers=[
                    {name:"Oxxo",internal_name:"OXXO"},
                    {name:"Otro",internal_name:"OTRO"}
                ];
            	
            	return providers;
            	
            } ,
            
           
           
             getData: function() {
                 console.log('getData');

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
