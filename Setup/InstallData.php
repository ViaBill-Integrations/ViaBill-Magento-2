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
        $setup->startSetup();
        
        $connection = $setup->getConnection();
        
        $moduleSetupTable = $setup->getTable('setup_module');
        if (!$this->isModuleSetup($connection, $moduleSetupTable)) {
            $moduleSetupItem = [
                'module' => 'Viabillhq_Payment',
                'schema_version' => '0.0.3',
                'data_version' => '0.0.3'
            ];
            $connection->insertOnDuplicate($setup_module_table, $moduleItem);
        }
        
        $configTable = $setup->getTable('core_config_data');
        foreach ($this->configPaths as $configPath) {
            $configItem = [
                'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                'scope_id' => 0,
                'path' => $configPath,
                'value' => null
            ];
            $connection->insertOnDuplicate($configTable, $configItem, ['value']);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $connection
     * @param string $tableName
     */
    public function isModuleSetup($connection, $tableName)
    {
        $select = $connection->select()->from($tableName, 'module')->where('module = :module');
        $moduleName = 'Viabillhq_Payment';
        $bind = [':module' => (string) $moduleName];
        $found = (string) $connection->fetchOne($select, $bind);
        if (empty($found)) {
            return false;
        } else {
            return true;
        }
    }
}
