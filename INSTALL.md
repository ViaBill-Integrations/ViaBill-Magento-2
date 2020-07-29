# ViaBill - seamless financing! Module for Magento 2: Installation

# Prerequisites

1. [Magento 2.2](https://devdocs.magento.com/guides/v2.2/release-notes/bk-release-notes.html) or [Magento 2.3](https://devdocs.magento.com/guides/v2.3/release-notes/bk-release-notes.html)
1. SSL must be installed on your site and active on your Checkout pages.
1. As with _all_ Magento extensions, it is highly recommended to backup your site before installation and to install and test on a staging environment prior to production deployments.

# Installation via Composer

1. Navigate to your [Magento root directory](https://devdocs.magento.com/guides/v2.2/extension-dev-guide/build/module-file-structure.html).
1. Enter command: `composer require viabillhq/module-payment`
1. Enter command: `php bin/magento module:enable Viabillhq_Payment` 
1. Enter command: `php bin/magento setup:upgrade`
1. Put your Magento in production mode if it’s required.

# Installation via Marketplace

Please follow [these instructions](https://devdocs.magento.com/extensions/install/) to install the module through the Magento Marketplace.

# Configuration

From Magento Admin navigate to `Store -> Configuration -> Sales -> Payment Methods` section. On the Payments Methods page the ViaBill Payments method should be listed together with other installed payment methods in a system.

By clicking the `Configure` button, all configuration module settings will be shown. Once you have finished with the configuration simply click Close and Save button for your convenience.

# Settings

ViaBill Payments configuration is divided by sections. It helps to quickly find and manage settings of each module feature:

1. Access ViaBill
1. Module Configuration
1. PriceTag Settings

First of all you have to create a new user in ViaBill System or sign in if you already have an account

New user

![](https://i.imgur.com/jbDIwCT.png)

or existing one

![](https://i.imgur.com/FINFLD1.png)

Second step you have to configure your module that is quite standard for Magento payment methods

![](https://i.imgur.com/h6uBaZH.png)

PriceTag Settings section allows you to specify additional module configuration

![](https://i.imgur.com/WsCXnfU.png)

# Support

Magento is an open source ecommerce solution: https://magento.com

Magento Inc is an Adobe company: https://magento.com/about

For Magento support, see Magento Help Center: https://support.magento.com/hc/en-us
