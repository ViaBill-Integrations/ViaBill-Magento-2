<?php
/**
 * Adminhtml header notices block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Viabillhq\Payment\Block\Page;

/**
 * @api
 * @since 100.0.2
 */
class Notices extends \Magento\Backend\Block\Template
{
    /**
     * Check if ViaBill test transaction store notice should be displayed.
     *
     * @return boolean
     */
    public function displayTestTransactionNotice()
    {
        return $this->_scopeConfig->getValue(
            'payment/viabill/test_transaction',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
