/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */

define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals'

], function (ko, Component, quote, totals) {

    return Component.extend({

        defaults: {
            template: 'Viabillhq_Payment/viabillPriceTag-checkout'
        },

        totals: quote.getTotals(),
        price: ko.observable(0),
        viabillScript: undefined,
        storeLanguage: window.checkoutConfig.payment.viabill.priceTag.language,
        storeCurrency: window.checkoutConfig.payment.viabill.priceTag.currency,
        countryCode: window.checkoutConfig.payment.viabill.priceTag.countryCode,

        initialize: function (config) {
            this._super();

            this.price.subscribe(function () {
                this.loadViabillScriptCheckout();
            }, this);

            totals.totals.subscribe(function () {
                this.setPrice(totals.getSegment('grand_total').value);
            }, this);
        },

        setPrice: function (price) {
            if (this.totals()) {
                this.price(price);
            }
        },
        loadViabillScriptCheckout: function () {
            var viabillScript = window.checkoutConfig.payment.viabill.priceTagScript;
            jQuery('#viabillScript').append(viabillScript);
        }
    });
})