<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement as Element;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Data\Form\FormKey;
use Viabillhq\Payment\Gateway\Command\ViabillCommandPool;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;
use Viabillhq\Payment\Model\Adminhtml\Source\MyViaBill;
use Viabillhq\Payment\Model\UrlProvider;

class CredentialsForm extends \Magento\Config\Block\System\Config\Form\Field
{
    const LOGIN_TEMPLATE = 'system/config/login_form_actions.phtml';
    const REGISTRATION_TEMPLATE = 'system/config/registration_form_actions.phtml';

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $template;

    /**
     * @var MyViaBill
     */
    private $myViaBill;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * CredentialsForm constructor.
     *
     * @param Context $context
     * @param LocaleResolver $localeResolver
     * @param UrlProvider $urlProvider
     * @param MyViaBill $myViaBill
     * @param FormKey $formKey
     * @param string $command
     * @param string $template
     * @param array $data
     */
    public function __construct(
        Context $context,
        LocaleResolver $localeResolver,
        UrlProvider $urlProvider,
        MyViaBill $myViaBill,
        FormKey $formKey,
        string $command = '',
        string $template = '',
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->urlProvider = $urlProvider;
        $this->command = $command;
        $this->template = $template;
        $this->myViaBill = $myViaBill;
        $this->formKey = $formKey;
    }

    /**
     * @return null|string
     */
    public function getMyViaBillUrl()
    {
        return $this->myViaBill->getMyViaBillUrl();
    }

    /**
     * @return string
     */
    public function getForgotPasswordUrl() : string
    {
        $locale = $this->localeResolver->getLocale();
        return $this->urlProvider->getForgotPasswordUrl($locale);
    }

    /**
     * @return string
     */
    public function getTermsAndConditionsUrl() : string
    {
        return $this->urlProvider->getTermsAndConditionsUrl();
    }

    /**
     * @return string
     */
    public function getCredentialsRequestUrl() : string
    {
        return $this->urlProvider->getUrl('viabill/account/getcredentials');
    }

    /**
     * @return string
     */
    public function getCommand() : string
    {
        return $this->command;
    }

    /**
     * Form Key is used to protect agains CSFR attacks
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return bool
     */
    public function isButtonVisible()
    {
        $key = $this->getConfigData(AccountConfiguration::CONFIG_PATH_VIABILL_API_KEY);
        return empty($key) || $this->command !== ViabillCommandPool::COMMAND_ACCOUNT_REGISTER;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(Element $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate($this->template);
        }

        return $this;
    }

    /**
     * Get the button contents
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(Element $element)
    {
        return $this->_toHtml();
    }
}
