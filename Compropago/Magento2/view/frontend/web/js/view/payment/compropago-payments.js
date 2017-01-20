
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
                component: 'Compropago_Magento2/js/view/payment/method-renderer/compropago-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);


window.onload = function(){
    var items = document.querySelectorAll("[id^=compropago_]");

    for(var x = 0; x < items.length; x++){
        items[x].addEventListener('click', function(evt){
            elem = evt.target;

            console.log(elem.getAttribute('id'));
        });
    }

    
};