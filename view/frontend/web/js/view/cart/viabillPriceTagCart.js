/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'ko',
    'Viabillhq_Payment/js/view/viabill',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals'
], function (ko, Component, quote, totals) {
    return Component.extend({
        totals: quote.getTotals(),
        price: ko.observable(0),
        isViabillScriptLoaded: false,
        storeLanguage: window.checkoutConfig.payment.viabill.priceTag.language,
        storeCurrency: window.checkoutConfig.payment.viabill.priceTag.currency,
        countryCode: window.checkoutConfig.payment.viabill.priceTag.countryCode,


        initialize: function (config) {
            this._super();

            this.price.subscribe(function () {
                this.loadViabillScript();
            }, this);

            totals.totals.subscribe(function () {
                this.setPrice(totals.getSegment('grand_total').value);
            }, this);
        },

        setPrice: function (price) {
            if (this.totals()) {
                this.price(price);
            }
        }
    });
})