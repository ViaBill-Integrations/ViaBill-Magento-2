<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Model\UrlProvider;

class ModuleInfoField extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var File
     */
    protected $fileIO;

    /**
     * ModuleInfoField constructor.
     *
     * @param Context $context
     * @param UrlProvider $urlProvider
     * @param LoggerInterface $logger
     * @param FormKey $formKey
     * @param DirectoryList $directory
     * @param StoreManagerInterface $storeManager
     * @param File $fileIO
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlProvider $urlProvider,
        LoggerInterface $logger,
        FormKey $formKey,
        DirectoryList $directory,
        StoreManagerInterface $storeManager,
        File $fileIO,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->urlProvider = $urlProvider;
        $this->formKey = $formKey;
        $this->directory = $directory;
        $this->storeManager = $storeManager;
        $this->fileIO = $fileIO;
        parent::__construct($context, $data);
    }

    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';

        $module_info_label = __('Module Version');
        $system_info_label = __('System Info');
        
        try {
            // Get Module Version
            $module_version = '4.0.36';
                        
            $module_info_data = $module_version;

            // Get PHP info
            $php_version = phpversion();
            $memory_limit = ini_get('memory_limit');

            // Get Magento Version
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata =
                $objectManager->get('Magento\Framework\App\ProductMetadataInterface'); // @codingStandardsIgnoreLine
            $magento_version = $productMetadata->getVersion();
            
            // Log data
            $error_file_path = $this->directory->getPath('log').'/viabill_critical.log';
            $debug_file_path = $this->directory->getPath('log').'/viabill_debug.log';
            
            $system_info_data = '<ul>'.
                '<li><strong>Magento Version</strong>: '.$magento_version.'</li>'.
                '<li><strong>PHP Version</strong>: '.$php_version.'</li>'.
                '<li><strong>Memory Limit</strong>: '.$memory_limit.'</li>'.
                '<li><strong>OS</strong>: '.PHP_OS.'</li>'.
                '<li><strong>Debug File</strong>: '.$debug_file_path.'</li>'.
                '<li><strong>Error File</strong>: '.$error_file_path.'</li>'.
                '</ul>';
            
            $system_params = [
                    'module_version'=>$module_version,
                    'magento_version'=>$magento_version,
                    'php_version'=>$php_version,
                    'memory_limit'=>$memory_limit,
                    'os'=>PHP_OS,
                    'debug_file'=>$debug_file_path,
                    'error_file'=>$error_file_path
                ];
            
            $email_support = $this->getSupportEmail($system_params);
            
            $contact_form = $this->getSupportForm($system_params);

            $system_info_data .= $email_support. '<br/>'. $contact_form;

        } catch (\Exception $e) { // @codingStandardsIgnoreLine
            $module_info_data = 'N/A';
            $system_info_data = 'N/A';
                                    
            $this->logger->critical($e->getMessage());
        }
                            
        $html .= '<tr id="module_info">
            <td class="label"><label><span data-config-scope="[GLOBAL]">'.
                $module_info_label.'</span></label></td>
            <td class="value" style="width:66%;"><span class="system_data">'.$module_info_data.'</span></td></tr>';

        $html .= '<tr id="system_info">
            <td class="label"><label><span data-config-scope="[GLOBAL]">'.
                $system_info_label.'</span></label></td>
            <td class="value" style="width:66%; padding-left:12px;"><span class="system_data">'.
                $system_info_data.'</span></td></tr>';

        return $html;
    }

    /**
     * Get Support Email
     *
     * @param array $params
     *
     * @return string
     */
    protected function getSupportEmail($params)
    {
                
        $site_url = $this->storeManager->getStore()->getBaseUrl();
        $file_lines = 1;
        
        $debug_log_entries = 'N/A';
        if ($this->fileIO->fileExists($params['debug_file'])) {
            $debug_log_entries = $this->fileTail($params['debug_file'], $file_lines);
        }
        
        $error_log_entries = 'N/A';
        if ($this->fileIO->fileExists($params['error_file'])) {
            $error_log_entries = $this->fileTail($params['error_file'], $file_lines);
        }

        $email = 'tech@viabill.com';
        $subject = "Magento 2 - Technical Assistance Needed - {$site_url}";
        $body = "Dear support,\r\nI am having an issue with the ViaBill Payment Module.".
                "\r\nHere is the detailed description:\r\n".
                "\r\nType here ....\r\n".
                "\r\n ============================================ ".
                "\r\n[System Info]\r\n".
                "* Module Version: ".$params['module_version']."\r\n".
                "* Magento Version: ".$params['magento_version']."\r\n".
                "* PHP Version: ".$params['php_version']."\r\n".
                "* Memory Limit: ".$params['memory_limit']."\r\n".
                "* OS: ".$params['os']."\r\n".
                "* Debug File: ".$params['debug_file']."\r\n".
                "* Error File: ".$params['error_file']."\r\n";
        
        $html = '<a href="mailto:'.$email.'?subject='.rawurlencode($subject).
            '&body='.rawurlencode($body).'">Need support? Contact us at '.$email.'</a>';

        return $html;
    }

    /**
     * Get Support Form
     *
     * @param array $system_params
     *
     * @return string
     */
    protected function getSupportForm($system_params = null)
    {

        $url = $this->urlProvider->getUrl('viabill/account/contactsupport');
        $html = 'Or use the <a href="'.$url.'" target="_blank">Contact form</a> instead.';

        return $html;
    }

    /**
     * Get File Tail
     *
     * @param string $filepath
     * @param int $num_of_lines
     *
     * @return string
     */
    protected function fileTail($filepath, $num_of_lines = 100)
    {
        $tail = '';
        
        $file = new \SplFileObject($filepath, 'r');
        $file->seek(PHP_INT_MAX);
        $last_line = $file->key();
        
        if ($last_line < $num_of_lines) {
            $num_of_lines = $last_line;
        }
        
        if ($num_of_lines>0) {
            $lines = new \LimitIterator($file, $last_line - $num_of_lines, $last_line);
            $arr = iterator_to_array($lines);
            $arr = array_reverse($arr);
            $tail = implode("", $arr);
        }
        
        return $tail;
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
}
