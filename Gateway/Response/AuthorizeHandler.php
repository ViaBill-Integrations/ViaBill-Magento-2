<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Response;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Type\Onepage as Checkout;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\Info as PaymentInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Payment;
use Viabillhq\Payment\Gateway\Request\SubjectReader;

class AuthorizeHandler implements HandlerInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * AuthorizeHandler constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param SubjectReader $subjectReader
     * @param CheckoutHelper $checkoutHelper
     * @param SessionManagerInterface $customerSession
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        SubjectReader $subjectReader,
        CheckoutHelper $checkoutHelper,
        SessionManagerInterface $customerSession
    ) {
        $this->subjectReader = $subjectReader;
        $this->cartRepository = $cartRepository;
        $this->checkoutHelper = $checkoutHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param array $handlingSubject
     * @param array $responseBody
     */
    public function handle(array $handlingSubject, array $responseBody)
    {
        /** @var PaymentInfo $payment */ /** @var Payment $payment */
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();
        $quote = $payment->getQuote();

        if (!$quote->getCheckoutMethod()) {
            if ($this->customerSession->isLoggedIn()) {
                $quote->setCheckoutMethod(Checkout::METHOD_CUSTOMER);
            } elseif ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Checkout::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(Checkout::METHOD_REGISTER);
            }
        }
        $quote->setIsActive(false);
        $this->cartRepository->save($quote);
    }
}
