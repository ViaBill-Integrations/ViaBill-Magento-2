<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * PriceTagProcessor constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Event handler for payment_method_is_active
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment_method = $observer->getEvent()->getMethodInstance()->getCode();
        if ($payment_method == "viabill") {
            $is_available = $this->isViabillAvailable($observer);
            if (!$is_available) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        } elseif ($payment_method == "viabill_try") {
            $is_available = $this->isTryViabillAvailable($observer);
            if (!$is_available) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        }
    }

    /**
     * Check if Viabill Monthly Payments is available during checkout
     *
     * @param Observer $observer
     *
     * return boolean
     */
    protected function isViabillAvailable(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->hasCredentials()) {
            return false;
        }

        $hide = (bool) $this->config->getValue(
            'payment/viabill/hide_checkout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($hide) {
            return false;
        }
        return true;
    }

    /**
     * Check if Viabill TBYB is available during checkout
     *
     * @param Observer $observer
     *
     * return boolean
     */
    protected function isTryViabillAvailable(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->hasCredentials()) {
            return false;
        }

        $show_try = (bool) $this->config->getValue(
            'payment/viabill/active_try',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($show_try) {
            return true;
        }
        return false;
    }

    /**
     * The payment methods are not available until the ViaBill account
     * credentials (API key and secret) have been entered in the module
     * settings. The values are stored encrypted, so a non-empty stored
     * value is a sufficient presence check.
     *
     * @return bool
     */
    private function hasCredentials()
    {
        $apiKey = $this->config->getValue('payment/viabill/apikey');
        $secret = $this->config->getValue('payment/viabill/secret');
        return !empty($apiKey) && !empty($secret);
    }
}
