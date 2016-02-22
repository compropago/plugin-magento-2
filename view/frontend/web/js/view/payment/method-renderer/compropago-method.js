/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
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
            },
            
            getData: function() {

                var additionalData = null;
                var returnData=null;
                additionalData = {};
                additionalData['compropagoProvider'] = this.compropagoProvider();
               // additionalData[this.getTransportName()] = this.selectedBillingAgreement();
                console.log("Get Data:"+additionalData['compropagoProvider']);
                
               
                return {'method': this.item.method, 'additional_data': additionalData};
                
                console.log(returnData);
                
                return returnData;
            },
            
            /**
             * On Place Order Btn Submit 
             */
           /* validate: function() {
                var form = '#compropago-form';
                console.log("Validate:"+ this.compropagoProvider());
                return true;
              
            }*/
           
        });
    }
);
