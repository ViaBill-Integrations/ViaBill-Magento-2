<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var array
     */
    private $configPaths = [
        AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_EMAIL,
        AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_COUNTRY,
        AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_SHOP_URL,
        AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_CONTACT_NAME,
        AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_PHONE,
        AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_MERCHANT_NAME,
        AccountConfiguration::CONFIG_PATH_VIABILL_API_KEY,
        AccountConfiguration::CONFIG_PATH_VIABILL_SECRET,
        AccountConfiguration::CONFIG_PATH_VIABILL_PRICE_TAG_SCRIPT
    ];

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $table = $setup->getTable('core_config_data');
        $this->resetConfigData($connection, $table);
    }

    /**
     * @param $connection
     * @param string $table
     */
    private function resetConfigData(AdapterInterface $connection, $table)
    {
        foreach ($this->configPaths as $configPath) {
            $configItem = [
                'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                'scope_id' => 0,
                'path' => $configPath,
                'value' => null
            ];
            $connection->insertOnDuplicate($table, $configItem, ['value']);
        }
    }
}
