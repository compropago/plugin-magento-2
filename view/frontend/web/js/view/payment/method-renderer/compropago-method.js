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

            /** Returns send check to info */
            showProviders: function() {
                return window.checkoutConfig.payment.compropago.compropagoProvidersDisplay;
            	
            },
            getData: function() {

                var additionalData = null;
               
                additionalData = {};
                additionalData['compropagoProvider'] = this.compropagoProvider();
                console.log(additionalData);
                
                return {'method': this.item.method, 'additional_data': additionalData};
            },
           /* validate: function() {
                var form = '#compropago-form';
                console.log(this.compropagoProvider());
              //  return $(form).validation() && $(form).validation('isValid');
                return true;
            }*/
           
        });
    }
);
