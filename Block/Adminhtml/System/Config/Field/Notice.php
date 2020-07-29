<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config\Field;

/**
 * Class Notice
 * @package Viabillhq\Payment\Block\Adminhtml\System\Config\Field
 */
class Notice extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return sprintf(
            '<tr id="row_%s"><td/><td colspan="1"><p class="note" id="%s">%s</p></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            $element->getLabel()
        );
    }
}
