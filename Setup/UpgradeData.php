<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $pathMap = [
                'payment/viabill_price_tag/show_on_product' => 'payment/viabill/price_tag_show_on_product',
                'payment/viabill_price_tag/show_on_cart' => 'payment/viabill/price_tag_show_on_cart',
                'payment/viabill_price_tag/show_on_checkout' => 'payment/viabill/price_tag_show_on_checkout',
            ];
            $connection = $setup->getConnection();
            foreach ($pathMap as $wrongPath => $correctPath) {
                $connection->update(
                    $setup->getTable('core_config_data'),
                    ['path' => $correctPath],
                    $connection->quoteInto('path = ?', $wrongPath)
                );
            }
        }
    }
}
