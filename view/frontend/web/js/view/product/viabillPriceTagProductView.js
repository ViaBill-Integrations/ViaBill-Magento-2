/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'jquery',
    'Viabillhq_Payment/js/view/viabill',
    'domReady!'
], function ($, Component) {
    return Component.extend({
        storeLanguage: window.priceTag.language,
        storeCurrency: window.priceTag.currency,
        countryCode: window.priceTag.countryCode,

        initialize: function () {
            this._super();
            //Product view actions
        }
    });
})