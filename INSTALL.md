# ViaBill - Seamless Financing!
### Module for Magento 2
## Prerequisites
1. Compatible Magento 2 Version: The ViaBill Payment module is compatible with Magento 2.3.x and early versions of 2.4.x. Compatibility with newer versions has not been tested but is likely.
2. SSL Requirement: SSL must be installed and active, especially on Checkout pages.
3. Backup Your Site: Always back up your site before installation. Install and test on a staging environment prior to deploying to production.
4. Environment Requirements: Ensure your server meets the following:
- PHP version compatible with your Magento version.
- Composer installed and compatible with your Magento version.
- At least 2GB of PHP memory available.

# Installation
### Installation via Composer
This is the recommended method for installing the ViaBill Payment module as it ensures all dependencies are handled automatically.

1. Log in to your server via SSH and navigate to the Magento 2 root folder.
2. Run the following commands:
```sh
composer require viabillhq/module-payment
php bin/magento module:enable Viabillhq_Payment
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento indexer:reindex
```
The module should now be installed and enabled.

## Installation via app/code Folder
Use this method if Composer is unavailable or the PHP memory limit is insufficient for Composer-based installation.

1. Download the module files from the public repository.
2. Copy the contents of the viabillhq/module-payment folder into `{Magento root}`/app/code/Viabillhq/Payment.
3. Run the following commands:
```sh
php bin/magento module:enable Viabillhq_Payment
php bin/magento setup:di:compile
php bin/magento setup:upgrade
php bin/magento cache:flush
php bin/magento indexer:reindex
```
The module should now be installed and enabled.
## Configuration
1. Log in to the Magento Admin panel.
2. Navigate to Stores → Configuration → Sales → Payment Methods.
3. Locate the ViaBill payment method and click to configure.

## Module Settings
1. Enable: Set this to "Yes" to enable ViaBill as a payment method.
2. Test Transactions Mode: Use "Yes" for sandbox mode; set to "No" for live transactions.
3. Debug Mode: Enable for troubleshooting and log generation.

## Price Tag Settings
If the ViaBill Price Tag feature is enabled, a small information box will appear below product prices or the cart total to indicate installment details.

# Upgrading the Module
## Upgrade via Composer
If the module was installed via Composer, use this method:

1. Log in via SSH and navigate to the Magento 2 root folder.
2. Run the following commands:
```sh
composer update viabillhq/module-payment
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
php bin/magento indexer:reindex
```

## Upgrade via app/code Folder
If the module was installed manually, follow these steps:

1. Download the latest module version from the public repository.
2. Replace the contents of `{Magento root}`/app/code/Viabillhq/Payment with the new version.
3. Run the following commands:
```sh
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
php bin/magento indexer:reindex
```

## Disabling the Module
To disable the ViaBill module without uninstalling it:

1. Navigate to Stores → Configuration → Sales → Payment Methods.
2. Set "Enable" to "No" in the ViaBill module settings.
3. Clear the Magento cache:
```sh
php bin/magento cache:flush
```

## Uninstalling the Module
To completely remove the ViaBill module:

### If Installed via Composer:
1. Log in via SSH and navigate to the Magento 2 root folder.
2. Run the following commands:
```sh
php bin/magento module:disable Viabillhq_Payment --clear-static-content
composer remove viabillhq/module-payment
php bin/magento setup:upgrade
```

### If Installed via app/code:
1. Log in via SSH and navigate to the Magento 2 root folder.
2. Run the following commands:
```sh
php bin/magento module:disable Viabillhq_Payment --clear-static-content
php bin/magento setup:upgrade
rm -rf app/code/Viabillhq/Payment
php bin/magento cache:flush
```

## Troubleshooting
1. Enable Debug Mode: Set Debug Mode to "Yes" in the module configuration to generate logs.
2. Contact Technical Support: If the issue persists, contact ViaBill support at tech@viabill.com.


