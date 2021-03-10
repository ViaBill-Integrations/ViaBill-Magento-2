<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Plugin\Sales\Model\Order\Email\Container;

class OrderIdentityPlugin
{
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsEnabled(
        \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject,
        callable $proceed
    ) {
        $returnValue = $proceed();

        if ($this->checkoutSession->hasQuote()) {
            $quote =  $this->checkoutSession->getQuote();
            if (!empty($quote)) {
                $paymentMethod = $quote->getPayment()->getMethod();
                if ($paymentMethod === 'viabill') {
                    $returnValue = false;
                }
            }
        }

        return $returnValue;
    }
}
