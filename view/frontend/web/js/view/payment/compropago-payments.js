
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
                type: 'compropago',
                component: 'Compropago_Payments/js/view/payment/method-renderer/compropago-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);