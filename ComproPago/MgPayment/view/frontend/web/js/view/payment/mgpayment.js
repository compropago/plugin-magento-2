/**
 * ComproPago_MgPayment Magento JS component
 *
 * @category    ComproPago
 * @package     ComproPago_MgPayment
 * @author      Eduardo Aguilar
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'compropago_mgpayment',
                component: 'ComproPago_MgPayment/js/view/payment/method-renderer/mgpayment-method'
            }
        );
        /** 
         * Add view logic here if needed 
         */
        return Component.extend({});
    }
);