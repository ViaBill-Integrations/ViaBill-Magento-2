/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
		'Magento_Checkout/js/model/totals',
		'ko'
    ], function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        fullScreenLoader,
        errorProcessor,
        customerData,
        quote, 
		totals,
		ko
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Viabillhq_Payment/payment/form_try',
            },

            totals: quote.getTotals(),
			price: ko.observable(0),
			storeLanguage: window.checkoutConfig.payment.viabill.priceTag.language,
			storeCurrency: window.checkoutConfig.payment.viabill.priceTag.currency,
			countryCode: window.checkoutConfig.payment.viabill.priceTag.countryCode,

			initialize: function () {
                this._super();
				this.setPrice(totals.getSegment('grand_total').value);
            },

			setPrice: function (price) {
				if (this.totals()) {
					this.price(price);
				}
			},

            /** Redirect to viabill */
            continueToViabill: function () {
                event.preventDefault();
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    var self = this;
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            $.post(
                                window.checkoutConfig.payment.viabill_try.authorizeUrl
                            ).done(
                                function (response) {
                                    if (response.hasOwnProperty('url')) {
                                        $.mage.redirect(response.url);
                                    } else if (response.hasOwnProperty('errorMessage')) {
                                        errorProcessor.process(response, self.messageContainer);
                                        fullScreenLoader.stopLoader();
                                    } else {
                                        customerData.invalidate(['cart']);
                                        window.location.reload();
                                    }
                                }
                            ).fail(
                                function (response) {
                                    fullScreenLoader.stopLoader();
                                    errorProcessor.process(response, self.messageContainer);
                                }
                            );
                        }.bind(this)
                    )
                }
                return false;
            },

            /**
             * Get payment method description.
             */
            getDescription: function () {
                return window.checkoutConfig.payment.viabill_try.description;
            },

            getLogoUrl: function () {
                return window.checkoutConfig.payment.viabill_try.logo;
            }
        });
    }
);
