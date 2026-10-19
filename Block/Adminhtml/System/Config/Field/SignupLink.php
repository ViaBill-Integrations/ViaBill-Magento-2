<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;

/**
 * Link to the ViaBill online signup form, shown next to the account credentials.
 */
class SignupLink extends Field
{
    public const SIGNUP_URL = 'https://portal.viabill.com/signup';

    /**
     * Render the signup link row
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return sprintf(
            '<tr id="row_%s"><td class="label"></td><td class="value"><p>%s '
            . '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a></p></td><td></td></tr>',
            $this->_escaper->escapeHtmlAttr($element->getHtmlId()),
            $this->_escaper->escapeHtml(__('No ViaBill account yet?')),
            $this->_escaper->escapeUrl($this->getSignupUrl()),
            $this->_escaper->escapeHtml(__('Sign up for ViaBill'))
        );
    }

    /**
     * Get the ViaBill signup form URL for this shop
     *
     * @return string
     */
    public function getSignupUrl(): string
    {
        $params = [
            'utm_source' => 'magento2',
            'utm_medium' => 'plugin',
            'utm_campaign' => 'merchant_signup',
            'shop_url' => $this->getShopUrl(),
        ];
        return self::SIGNUP_URL . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Get the base URL of the default store view
     *
     * @return string
     */
    private function getShopUrl(): string
    {
        try {
            $store = $this->_storeManager->getDefaultStoreView();
            $url = $store ? (string) $store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true) : '';
        } catch (\Exception $e) { // @codingStandardsIgnoreLine
            $url = '';
        }
        return rtrim($url, '/');
    }
}
