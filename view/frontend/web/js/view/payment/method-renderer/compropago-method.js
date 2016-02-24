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
            	var providers={[{"name":"Oxxo","store_image":"oxxo.png","is_active":true,"internal_name":"OXXO","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-oxxo-medium.png","image_large":null,"rank":1},{"name":"7Eleven","store_image":"seven.png","is_active":true,"internal_name":"SEVEN_ELEVEN","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-seven-medium.png","image_large":null,"rank":2},{"name":"Chedraui","store_image":"chedraui.png","is_active":true,"internal_name":"CHEDRAUI","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-chedraui-medium.png","image_large":null,"rank":3},{"name":"Coppel","store_image":"coppel.png","is_active":true,"internal_name":"COPPEL","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-coppel-medium.png","image_large":null,"rank":4},{"name":"Extra","store_image":"extra.png","is_active":true,"internal_name":"EXTRA","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-extra-medium.png","image_large":null,"rank":5},{"name":"Farmacia Benavides","store_image":"farmacia_benavides.png","is_active":true,"internal_name":"FARMACIA_BENAVIDES","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-benavides-medium.png","image_large":null,"rank":6},{"name":"Farmacia Esquivar","store_image":"farmacia_esquivar.png","is_active":true,"internal_name":"FARMACIA_ESQUIVAR","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-esquivar-medium.png","image_large":null,"rank":7},{"name":"Elektra","store_image":"elektra.png","is_active":true,"internal_name":"ELEKTRA","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-elektra-medium.png","image_large":null,"rank":9},{"name":"Pitic\u00f3","store_image":"pitico.png","is_active":true,"internal_name":"PITICO","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-pitico-medium.png","image_large":null,"rank":11},{"name":"Telecomm","store_image":"telecomm.png","is_active":true,"internal_name":"TELECOMM","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-telecomm-medium.png","image_large":null,"rank":12},{"name":"Farmacia ABC","store_image":"abc.png","is_active":true,"internal_name":"FARMACIA_ABC","image_small":null,"image_medium":"https:\/\/s3.amazonaws.com\/compropago\/assets\/images\/receipt\/receipt-abc-medium.png","image_large":null,"rank":13}]}
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
