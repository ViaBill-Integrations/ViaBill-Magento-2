/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
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
                type: 'viabill_try',
                component: 'Viabillhq_Payment/js/view/payment/method-renderer/viabill_try'
            }
        );		
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
