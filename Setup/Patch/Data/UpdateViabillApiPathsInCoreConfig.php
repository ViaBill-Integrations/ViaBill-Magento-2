<?php

namespace Viabillhq\Payment\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Renames legacy ViaBill config paths to their correct locations.
 * Idempotent: safe to run once; subsequent runs find nothing to update.
 */
class UpdateViabillApiPathsInCoreConfig implements DataPatchInterface, PatchVersionInterface
{
    /**
     * Patch constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup Module data setup instance
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * Apply the data patch: rename misnamed config paths in core_config_data.
     *
     * @return void
     */
    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $this->moduleDataSetup->getConnection()->startSetup();

        // Map of wrong => correct paths
        $pathMap = [
            'payment/viabill_price_tag/show_on_product'  => 'payment/viabill/price_tag_show_on_product',
            'payment/viabill_price_tag/show_on_cart'     => 'payment/viabill/price_tag_show_on_cart',
            'payment/viabill_price_tag/show_on_checkout' => 'payment/viabill/price_tag_show_on_checkout',
        ];

        $table = $this->moduleDataSetup->getTable('core_config_data');

        foreach ($pathMap as $wrongPath => $correctPath) {
            // If a row exists with the wrong path, rename it to the correct path.
            $connection->update(
                $table,
                ['path' => $correctPath],
                $connection->quoteInto('path = ?', $wrongPath)
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Patches that must run before this one.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Aliases for this patch (if it replaces older ones).
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Version of this patch.
     *
     * @return string
     */
    public static function getVersion(): string
    {
        return '0.0.3';
    }
}
