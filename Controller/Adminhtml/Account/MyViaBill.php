<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Controller\Adminhtml\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Viabillhq\Payment\Model\Adminhtml\Source\MyViaBill as LinkProvider;

class MyViaBill extends Action
{
    /**
     * @var LinkProvider
     */
    private $myViaBill;

    /**
     * @var string
     */
    private $refererURL;

    /**
     * MyViaBill constructor.
     *
     * @param Context $context
     * @param LinkProvider $myViaBill
     */
    public function __construct(
        Context $context,
        LinkProvider $myViaBill
    ) {
        $this->myViaBill = $myViaBill;
        $this->refererURL = $context->getRedirect()->getRefererUrl();
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $myViaBillUrl = $this->myViaBill->getMyViaBillUrl();
        $url = $myViaBillUrl ?? $this->refererURL;
        $result->setUrl($url);
        return $result;
    }
}
