<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Plugin\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;

class Toolbar
{
    /**
     * Before Push Button
     *
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     *
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View
            || $context->getOrder()->getState() !== \Magento\Sales\Model\Order::STATE_PROCESSING
        ) {
            return [$context, $buttonList];
        }
        $message = __(
            'This will renew the order on viabill. ' .
            'Do you want to continue?'
        );
        $buttonList->add(
            'order_renew',
            [
                'label' => __('Renew'),
                'onclick' => "confirmSetLocation('{$message}','{$context->getUrl('viabill/order/renew')}')",
                'class' => 'renew'
            ]
        );
        return [$context, $buttonList];
    }
}
