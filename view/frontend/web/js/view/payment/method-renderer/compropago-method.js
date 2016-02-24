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

          
            
            /** Returns providers json */
            getCompropagoProviders: function() {
            	var providers=[{name:"Oxxo",internal_name:"OXXO"},{name:"Otro",internal_name:"OTRO"}]
            	
            	return providers;
            	
            } ,
            
           
           
             getData: function() {
            	console.log("po_number:"+this.compropagoProvider());
            	/*var d = new Date();
                d.setTime(d.getTime() + (1*24*60*60*1000));
                var expires = "expires="+d.toUTCString(); 
            	document.cookie ="cpProvider="+ this.compropagoProvider() + ";" + expires;*/
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
