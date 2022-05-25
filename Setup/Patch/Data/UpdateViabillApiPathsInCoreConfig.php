<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Viabillhq\Payment\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;

class UpdateViabillApiPathsInCoreConfig implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ModuleContextInterface
     */
    private $context;

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
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ModuleContextInterface $context
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup, ModuleContextInterface $context)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->context = context;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->updateViabillApiPathKeys();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '0.0.3';
    }

    /**
     * Updating the Viabill api path keys
     */
    private function updateViabillApiPathKeys()
    {
        $setup = $this->moduleDataSetup;
        $connection = $setup->getConnection();
        
        $moduleSetupTable = $setup->getTable('setup_module');
        if (!$this->isModuleSetup($connection, $moduleSetupTable)) {
            $moduleSetupItem = [
                'module' => 'Viabillhq_Payment',
                'schema_version' => '0.0.3',
                'data_version' => '0.0.3'
            ];
            $connection->insertOnDuplicate($moduleSetupTable, $moduleSetupItem);
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
        
        $context_version = (empty($this->context))?null:$this->context->getVersion();
        if (isset($context_version)) {
            if (version_compare($context_version, '0.0.2', '<')) {
                $pathMap = [
                    'payment/viabill_price_tag/show_on_product' => 'payment/viabill/price_tag_show_on_product',
                    'payment/viabill_price_tag/show_on_cart' => 'payment/viabill/price_tag_show_on_cart',
                    'payment/viabill_price_tag/show_on_checkout' => 'payment/viabill/price_tag_show_on_checkout',
                ];
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

    /**
     * Check if module has been setup
     *
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
