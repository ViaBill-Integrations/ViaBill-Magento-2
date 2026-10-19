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
            // The checkout page has a strict Content Security Policy that blocks inline
            // scripts, so the PriceTag snippet is not executed: the script URL it loads
            // (pricetag.viabill.com, allowed in etc/csp_whitelist.xml) is loaded directly.
            var snippet = window.checkoutConfig.payment.viabill.priceTagScript || '',
                match = snippet.match(/src\s*=\s*['"](https:\/\/[^'"]+)['"]/i),
                container = document.getElementById('viabillScript'),
                script;

            if (!match || !container) {
                return;
            }
            script = document.createElement('script');
            script.async = true;
            script.src = match[1];
            container.appendChild(script);
        }
    });
})