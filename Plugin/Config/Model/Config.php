<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Plugin\Config\Model;

use Magento\Config\Model\Config as MagentoConfig;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class Config
{
    const CONFIG_PATH_VIABILL_ACTIVE = 'payment/viabill/active';
    const CONFIG_PATH_DEFAULT_CURRENCY = 'currency/options/default';

    /**
     * @var array
     */
    private static $allowedCurrencies = [
        'DKK',
        'NOK',
        'EUR',
        'USD'
    ];

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var ConfigResource
     */
    private $configResource;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param MessageManager $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigResource $configResource
     */
    public function __construct(
        MessageManager $messageManager,
        ScopeConfigInterface $scopeConfig,
        ConfigResource $configResource
    ) {
        $this->messageManager = $messageManager;
        $this->configResource = $configResource;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param MagentoConfig $subject
     */
    public function beforeSave(MagentoConfig $subject)
    {
        // Load and define scope information
        $subject->load();
        if ($this->isModuleEnabled($subject) && !$this->isAllowedCurrency($subject)) {
            $this->disableModule($subject);
            $this->messageManager->addErrorMessage(
                __('Viabill payment method has been disabled because it doesn\'t support current store currency.')
            );
        }
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     *
     * @return bool
     */
    private function isModuleEnabled(MagentoConfig $subject)
    {
        if ($subject->getDataByKey('section') === 'payment') {
            $groupsData = $subject->getDataByKey('groups');
            return $groupsData['viabill']['fields']['active']['value'] ?? false;
        }
        return (bool) $subject->getConfigDataValue(self::CONFIG_PATH_VIABILL_ACTIVE);
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     *
     * @return bool
     */
    private function isAllowedCurrency(MagentoConfig $subject)
    {
        if ($subject->getDataByKey('section') === 'currency') {
            $groupsData = $subject->getDataByKey('groups');
            $newCurrency = $groupsData['options']['fields']['default']['value'] ?? null;
            return in_array($newCurrency, self::$allowedCurrencies);
        }
        return in_array($this->getScopeCurrencyCode($subject), self::$allowedCurrencies);
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     */
    private function disableModule(MagentoConfig $subject)
    {
        if ($subject->getDataByKey('section') === 'payment') {
            $groupsData = $subject->getDataByKey('groups');
            $groupsData['viabill']['fields']['active']['value'] = 0;
            $subject->setData('groups', $groupsData);
        } else {
            $this->configResource->saveConfig(
                self::CONFIG_PATH_VIABILL_ACTIVE,
                0,
                $subject->getScope(),
                $subject->getScopeId()
            );
        }
    }

    /**
     * @param MagentoConfig $subject
     *
     * @return mixed
     */
    private function getScopeCurrencyCode(MagentoConfig $subject)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_DEFAULT_CURRENCY,
            $subject->getScope(),
            $subject->getScopeId()
        );
    }
}
