# ViaBill - seamless financing! 
## Module for Magento 2

# Prerequisites

1. A compatible Magento 2 version. Note that ViaBill Payment module is compatible with Magento 2.2.x, 2.3.x and 2.4.1. It may be compatible with newer versions as well, but it hasn't been tested.
2. SSL must be installed on your site and active on your Checkout pages.
3. As with _all_ Magento extensions, it is highly recommended to backup your site before installation and to install and test on a staging environment prior to production deployments.

# Installation

Before you start the installation of the module, make sure you meet the requirements set by your Magento version. More specifically, you should make sure that the PHP version and the Composer version is compatible with the Magento 2 version installed on your server. Also, make sure there is enough PHP memory (2GB or more) to complete the installation without errors.

## Installation via Composer

If you’re running the composer command for the first time, you may be asked for your Magento Marketplace account credentials (Public Key and Private Key). Therefore, if you haven't done it before, please go to https://magento.com/ and create an Magento Marketplace account to obtain the keys.
1. Log in to your server with SSH and go to the Magento 2 root folder.
2. Enter the following commands: 
```sh
composer require viabillhq/module-payment
php bin/magento module:enable Viabillhq_Payment
php bin/magento setup:upgrade
```

## Installation via app/code folder

This method is an alternative method to the installation via Composer, in case you have not enough PHP memory to complete the installation, or if the composer tool is not available.
1. Download the module files from a public repository
2. Copy the contents of the viabillhq/module-payment folder into the newly created `{magento 2 root folder}`/app/code/Viabillhq/Payment folder.
3. Enter the following commands:
```sh
php bin/magento module:enable Viabillhq_Payment
php bin/magento setup:di:compile
php bin/magento setup:upgrade
```

## Installation via Marketplace

Please follow these instructions to install the module through the Magento Marketplace. Note that this method is not available in all Magento versions.

# Configuration

From Magento Admin navigate to Stores -> Configuration -> Sales -> Payment Methods section. On the Payments Methods page ViaBill should be listed together with other installed payment methods in the system.

## New or Existing User

Before configuring the module, you need to create a new ViaBill account or sign in, if you already have an existing one.

## Module Configuration

Once you have created successfully your ViaBill account, or login into your existing one, you will be able to configure the payment module. Please pay attention to the following settings:
Enable: Set the parameter to “Yes” to enable this payment method.
Test Transactions Mode: If this parameter is set to “Yes”, no actual payment is made, therefore orders should not be shipped. Once you are ready to use ViaBill with real customers it's important to set this parameter to “No”.
Debug Mode: This parameter is useful if something is not working as expected and it can provide valuable information to the tech support team.

> Tip: If you can't save the module configuration settings without getting any errors, click on the “Configure” button for the PayPal Express Checkout  payment method that appears on the same page. Sometimes, the browser fills in partially the Express Checkout form fields and you have to manually clear these fields before clicking on the “Save Config” button.

## PriceTag Settings

If you enable the ViaBill Price Tag feature, then a small info box will appear below the product price and/or the cart total price to indicate the monthly installments.

# Upgrade Module

If you want to upgrade the ViaBill Payment Module, there are two methods to do that that are explained below. 

## Upgrade via Composer

This method is recommended if you have installed the ViaBill module using the Composer.

1. Log in to your server with SSH and go to the Magento 2 root folder.
2. Enter the following commands
```sh
rm -rf var/page_cache var/cache var/composer_home var/generation var/di var/view_preprocessed
composer update vendor/module-name
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
php bin/magento indexer:reindex
```
## Upgrade manually

This method is recommended only if you have not installed the module via Composer. 

1. Download the module files from a public repository
2. Copy the contents of the viabillhq/module-payment folder into the existing folder, which can be one of the following: `{magento 2 root folder}`/app/code/Viabillhq/Payment or `{magento 2 root folder}`/vendor/viabillhq/module-payment
3. Log in to your server with SSH and go to the Magento 2 root folder.
4. Enter the following commands
```sh 
rm -rf var/page_cache var/cache var/composer_home var/generation var/di var/view_preprocessed
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
php bin/magento indexer:reindex
```
# Disable Module

If you wish disable the ViaBill module without uninstall it, you can simply go to the module configuration page by navigating to Stores -> Configuration -> Sales -> Payment Methods and inside the ViaBill Module Configuration set the “Enable” parameter to No. 

You mean need to clear the Magento 2 cache to see the changes in the front-end.

# Uninstall Module

If you want to completely remove the ViaBill module you will need shell access. More specifically:

1. Log in to your server with SSH and go to the Magento 2 root folder.
2. Enter the commands: 
```sh
php bin/magento module:disable Viabillhq_Payment --clear-static-content 
php bin/magento setup:upgrade
uninstall -r Viabillhq_Payment
```
# Troubleshooting and Support

## ViaBill Module Support

If you are experiencing any technical issues, please navigate to Stores -> Configuration -> Sales -> Payment Methods and under the ViaBill Module Configuration section set the Debug Mode parameter to “Developer”. Then try to replicate your issue by repeating the action which caused it. Finally, click on the “Contact Form” link that you will find under the ViaBill Module Info section. Fill out the form and submit it to our technical support team. This contact form is auto-populated with vital information that will help us to resolve your issue faster.

Alternatively, contact us via email at tech@viabill.com.

## Magento Support

Magento is an open source ecommerce solution: https://magento.com
Magento Inc is an Adobe company: https://magento.com/about
For Magento support, see Magento Help Center: https://support.magento.com/hc/en-us 