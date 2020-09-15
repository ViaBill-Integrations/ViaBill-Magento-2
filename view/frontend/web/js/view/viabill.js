/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'ko',
    'uiComponent'
], function (ko, Component) {
    return Component.extend({
        defaults: {
            template: 'Viabillhq_Payment/viabillPriceTag'
        },
        isViabillScriptLoaded: false,

        loadViabillScript: function () {
            if (!this.isViabillScriptLoaded) {
                //templates/viabill-price-tag.phtml
                window.runViabillScript();
                this.isViabillScriptLoaded = true;
            }
        }
    });
})