/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
/*jshint jquery:true*/
define(
    [
        "jquery"
    ], function ($) {
        "use strict";
        $.widget(
            "viabill.request", {
                options: {
                    registerForm: undefined,
                    registerSubmitButton: '#viabil-register-btn',
                    registerUrl: undefined,
                    registerParam: undefined,

                    loginForm: undefined,
                    loginSubmitButton: '#viabil-login-btn',
                    loginUrl: undefined,
                    loginParam: undefined,

                    publicKeyField: '[data-ui-id="password-groups-viabill-fields-apikey-value"]',
                    pricetagScriptField: '[data-ui-id="textarea-groups-viabill-fields-price-tag-script-value"]',
                    secretField: '[data-ui-id="password-groups-viabill-fields-secret-value"]',

                    requestForm: '#viabill-request-form',
                    termsAndConditionsCheckbox: '#viabil-terms-and-conditions-checkbox',
                    isTermsAndConditionsCheckboxChecked: '#viabill-request-form:checkbox:checked',
                    termsAndConditionsLink: '#viabil-terms-and-conditions-link'

                },
                _create: function () {
                    this._bind();
                },
                _bind: function () {
                    this.addTermsAndConditionsLink();

                    $(this.options.requestForm).closest('tbody').find('select').on('change', this.addTermsAndConditionsLink.bind(this));
                    $(this.options.termsAndConditionsCheckbox).on('change', this.toggleSubmitButton.bind(this));

                    //Registration form
                    $(this.options.registerSubmitButton).on('click', function () {

                        var formData = this._getFormElements(this.options.registerForm, this.options.registerParam);
                        this._makeRequest(formData, this.options.registerUrl);

                    }.bind(this));

                    //Login form
                    $(this.options.loginSubmitButton).on('click', function () {

                        var formData = this._getFormElements(this.options.loginForm, this.options.loginParam);
                        this._makeRequest(formData, this.options.loginUrl);

                    }.bind(this));
                },
                _getFormElements: function (form, customParam) {
                    var $formElements = $(form).closest('fieldset').find('tbody tr');
                    var formData = {};

                    //Adding form custom param
                    if (customParam !== undefined) {
                        formData['command'] = customParam;
                    }
                    
                    //Adding form values
                    $.each($formElements, function () {                        
                        var formElement = $(this).find('input, select');
                        if (formElement.length>0) {						
                            var formElementValue = formElement.val();						
                            var formElementName = null;
                            var formElementNameRaw = formElement.attr('name');
                            if (typeof formElementNameRaw !== typeof undefined && formElementNameRaw !== false) {
                                var matchField = formElementNameRaw.match(/\[fields\]\[(.+)\]\[value\]/);
                                if (matchField) {
                                    formElementName = matchField[1];
                                } else {
                                    var formElementID = formElement.attr('id');
                                    if (typeof formElementID !== typeof undefined && formElementID !== false) {		
                                        if (formData['command'] == 'register') {
                                            matchField = formElementID.match(/reg_configuration_(.+)/);
                                            if (matchField) {
                                                formElementName = matchField[1];		
                                            }
                                        } else if (formData['command'] == 'login') {
                                            matchField = formElementID.match(/login_(.+)/);
                                            if (matchField) {
                                                formElementName = matchField[1];		
                                            }
                                        }
                                    }
                                }					
                                if (formElementName && formElementValue) {
                                    var fieldNameMappins = {};
                                    if (formData['command'] == 'register') {
                                        fieldNameMappins = {'contact_name':'name', 'shop_url':'url'};
                                    } else {
                                        fieldNameMappins = {'login_email':'email'};
                                    }
                                    if (formElementName in fieldNameMappins) {
                                        formElementName = fieldNameMappins[formElementName];
                                    }
                                    formData[formElementName] = formElementValue;		
                                }						                     
                            }
                        } else {
                            return;
                        }                                                             					
                    });

                    return formData;
                },

                /**
                 * Makes ajax request
                 *
                 * @param {Object} data
                 * @param {String} url
                 * @returns {*}
                 */
                _makeRequest: function (data, url) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        showLoader: true,
                        dataType: 'json',                        
                        
                        /**
                         * Success callback.
                         * @param {Object} resp
                         */
                        success: function (resp) {
                            if (resp !== null && resp.errorMessage !== undefined) {
                                alert(resp.errorMessage);
                            } else {
                                window.location.reload();
                            }
                        }.bind(this),

                        error: function (resp) {
                            if (resp !== null && resp.responseJSON !== undefined && resp.responseJSON.errorMessage !== undefined) {
                                alert(resp.responseJSON.errorMessage);
                            } else {
                                console.log('Response error');
                            }                            
                        }
                    });
                },
                isTermsAndConditionsChecked: function () {
                  return $(this.options.isTermsAndConditionsCheckboxChecked).length;
                },
                toggleSubmitButton: function () {
                    if (this.isTermsAndConditionsChecked) {
                        $(this.options.registerSubmitButton).toggleClass('disabled');
                    }
                },
                addTermsAndConditionsLink: function () {
                    var selectedCountryCode = $(this.options.requestForm).closest('tbody').find('option:selected').attr('value');
                    var viaBillUrl = $(this.options.termsAndConditionsLink).attr('data-link');

                    $(this.options.termsAndConditionsLink).attr('href', viaBillUrl + '#' + selectedCountryCode);
                }
            }

        );
        return $.viabill.request;
    }
);

