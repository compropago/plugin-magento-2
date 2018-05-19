
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
                type: 'compropago_cash',
                component: 'Compropago_Magento2/js/view/payment/method-renderer/compropago-method'
            },
            {
                type: 'compropago_spei',
                component: 'Compropago_Magento2/js/view/payment/method-renderer/compropago-spei'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
