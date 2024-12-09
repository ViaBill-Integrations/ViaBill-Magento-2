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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Model\UrlProvider;

class ContactSupport extends Action
{
    /**
     * The ViaBill module version
     */
    public const ADDON_VERSION = '4.0.39';

    /**
     * Contact From Action.
     */
    public const VIABILL_TECH_SUPPORT_EMAIL = 'tech@viabill.com';

    /**
     * Country config path.
     */
    public const CONFIG_PATH_VIABILL_ACCOUNT_COUNTRY = 'payment/viabill_account/country';

    /**
     * Email config path.
     */
    public const CONFIG_PATH_VIABILL_ACCOUNT_EMAIL = 'payment/viabill_account/email';
    
    /**
     * Number of lines to read from the log files (debug and error).
     */
    public const LOG_FILE_LINES_TO_READ = 150;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var File
     */
    protected $fileIO;
    
    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ContactSupport constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param DirectoryList $directory
     * @param File $fileIO
     * @param UrlProvider $urlProvider
     * @param Repository $repository
     * @param FormKey $formKey
     * @param Escaper $escaper
     * @param Resolver $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        DirectoryList $directory,
        File $fileIO,
        UrlProvider $urlProvider,
        Repository $repository,
        FormKey $formKey,
        Escaper $escaper,
        Resolver $localeResolver,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->directory = $directory;
        $this->fileIO = $fileIO;
        $this->urlProvider = $urlProvider;
        $this->repository = $repository;
        $this->formKey = $formKey;
        $this->escaper = $escaper;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->scopeConfig =  $scopeConfig;
        $this->request = $context->getRequest();

        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        try {
            $request = $this->getRequestedData();
            if ($request['output']) {
                $output_html = $this->getContactFormOutput($request);
                $result->setContents($output_html);
            } else {
                $contact_form_html = $this->getContactForm();
                $result->setContents($contact_form_html);
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        } catch (\Exception $e) { // @codingStandardsIgnoreLine
            $this->logger->critical($e->getMessage());
        }
        return $result;
    }

    /**
     * Get Contact Form
     *
     * @return string
     */
    protected function getContactForm()
    {
        // Get Module Version
        $module_version = self::ADDON_VERSION;

        // Get PHP info
        $php_version = phpversion();
        $memory_limit = ini_get('memory_limit');

        // Get Magento Version
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata =
            $objectManager->get('Magento\Framework\App\ProductMetadataInterface'); // @codingStandardsIgnoreLine
        $magento_version = $productMetadata->getVersion();
        
        // Get Store Info
        $langCode = $this->localeResolver->getLocale();
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $storeName = $this->storeManager->getStore()->getName();
        $storeURL = $this->storeManager->getStore()->getBaseUrl();
        
        // Get ViaBill Config
        $storeCountry = $this->scopeConfig->getValue(
            self::CONFIG_PATH_VIABILL_ACCOUNT_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $storeEmail = $this->scopeConfig->getValue(
            self::CONFIG_PATH_VIABILL_ACCOUNT_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        // Log data
        $error_file_path = $this->directory->getPath('log').'/viabill_critical.log';
        $debug_file_path = $this->directory->getPath('log').'/viabill_debug.log';
                
        $params = [
                'module_version'=>$module_version,
                'magento_version'=>$magento_version,
                'php_version'=>$php_version,
                'memory_limit'=>$memory_limit,
                'os'=>PHP_OS,
                'debug_file'=>$debug_file_path,
                'error_file'=>$error_file_path
            ];
                
        $site_url = $this->storeManager->getStore()->getBaseUrl();
        
        $file_lines = self::LOG_FILE_LINES_TO_READ;

        $debug_log_entries = 'N/A';
        if ($this->fileIO->fileExists($params['debug_file'])) {
            $debug_log_entries = $this->fileTail($params['debug_file'], $file_lines);
        }
        
        $error_log_entries = 'N/A';
        if ($this->fileIO->fileExists($params['error_file'])) {
            $error_log_entries = $this->fileTail($params['error_file'], $file_lines);
        }

        $action_url = $this->getActionURL();

        $terms_of_service_lang = strtolower(trim($langCode));
        switch ($terms_of_service_lang) {
            case 'us':
                $terms_of_use_url = 'https://viabill.com/us/legal/cooperation-agreement/';
                break;
            case 'es':
                $terms_of_use_url = 'https://viabill.com/es/legal/contrato-cooperacion/';
                break;
            case 'dk':
                $terms_of_use_url = 'https://viabill.com/dk/legal/cooperation-agreement/';
                break;
            default:
                $terms_of_use_url = 'https://viabill.com/dk/legal/cooperation-agreement/';
                break;
        }

        $form_key = $this->getFormKey();

        $html = '<!DOCTYPE html>
<html lang="en-US" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" 
    href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" 
    crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" crossorigin="anonymous">
    </script>	
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" crossorigin="anonymous"></script>
</head>
<body>                    
    <div class="container" style="margin-top: 20px;margin-bottom: 20px;">
    <h3>Support Request Form</h3>
    <div class="alert alert-primary" role="alert">
        Please fill out the form below and click on the <em>Send Support Request</em> 
            button to send your request.
    </div>
    <form id="tech_support_form" action="'.$action_url.'" method="post">
    <fieldset>
        <legend class="w-auto text-primary">Issue Description</legend>
        <div class="form-group">
            <label>Your Name</label>
            <input class="form-control" type="text" required="true" name="ticket_info[name]" value="" />
        </div>
        <div class="form-group">
            <label>Your Email</label>
            <input class="form-control" type="text" required="true" name="ticket_info[email]" value="" />
        </div>
        <div class="form-group">
            <label>Message</label>
            <textarea class="form-control" name="ticket_info[issue]" 
                placeholder="Type your issue description here ..." rows="10" required="true"></textarea>
        </div>
    </fieldset>
    <fieldset>
        <legend class="w-auto text-primary">Eshop Info</legend>
        <div class="form-group">
            <label>Store Name</label>
            <input class="form-control" type="text" required="true"
                 value="'.$storeName.'" name="shop_info[name]" />
        </div>                
        <div class="form-group">
            <label>Store URL</label>
            <input class="form-control" type="text" required="true"
             value="'.$storeURL.'" name="shop_info[url]" />
        </div>
        <div class="form-group">
            <label>Store Email</label>
            <input class="form-control" type="text" required="true"
             value="'.$storeEmail.'" name="shop_info[email]" />
        </div>
        <div class="form-group">
            <label>Eshop Country</label>
            <input class="form-control" type="text" required="true"
             value="'.$storeCountry.'" name="shop_info[country]" />
        </div>
        <div class="form-group">
            <label>Eshop Language</label>
            <input class="form-control" type="text" required="true"
             value="'.$langCode.'" name="shop_info[language]" />
        </div>
        <div class="form-group">
            <label>Eshop Currency</label>
            <input class="form-control" type="text" required="true"
             value="'.$currencyCode.'" name="shop_info[currency]" />
        </div>                
        <div class="form-group">
            <label>Module Version</label>
            <input class="form-control" type="text"
             value="'.$params['module_version'].'" name="shop_info[addon_version]" />
        </div>
        <div class="form-group">
            <label>Magento Version</label>
            <input type="hidden" value="magento" name="shop_info[platform]" />
            <input class="form-control" type="text"
             value="'.$params['magento_version'].'" name="shop_info[platform_version]" />
        </div>
        <div class="form-group">
            <label>PHP Version</label>
            <input class="form-control" type="text"
             value="'.$params['php_version'].'" name="shop_info[php_version]" />
        </div>
        <div class="form-group">
            <label>Memory Limit</label>
            <input class="form-control" type="text"
             value="'.$params['memory_limit'].'" name="shop_info[memory_limit]" />
        </div>
        <div class="form-group">
            <label>O/S</label>
            <input class="form-control" type="text"
             value="'.$params['os'].'" name="shop_info[os]" />
        </div>
        <div class="form-group">
            <label>Debug File</label>
            <input class="form-control" type="text"
             value="'.$params['debug_file'].'" name="shop_info[debug_file]" />
        </div>
        <div class="form-group">
            <label>Debug Data</label>
            <textarea class="form-control"
             name="shop_info[debug_data]">'.$this->escaper->escapeHtml($debug_log_entries).'</textarea>
        </div>
        <div class="form-group">
            <label>Error File</label>
            <input class="form-control" type="text"
             value="'.$params['error_file'].'" name="shop_info[error_file]" />
        </div>
        <div class="form-group">
            <label>Error Data</label>
            <textarea class="form-control" name="shop_info[error_data]">'.
            $this->escaper->escapeHtml($error_log_entries).'</textarea>
        </div>
    </fieldset>            
    <div class="form-group form-check">
        <input type="checkbox" value="accepted" required="true"
         class="form-check-input" name="terms_of_use" id="terms_of_use"/>
          <label class="form-check-label">I have read and accept the
           <a href="'.$terms_of_use_url.'">Terms and Conditions</a></label>
    </div>            
    <button type="button" onclick="validateAndSubmit()" class="btn btn-primary">Send Support Request</button>
    <input type="hidden" name="form_key" value="'.$this->escaper->escapeHtml($form_key).'" />
    </form>
    </div>
    <script>
    function validateAndSubmit() {
        var form_id = "tech_support_form";
        var error_msg = "";
        var valid = true;
        
        jQuery("#" + form_id).find("select, textarea, input").each(function() {
            if (jQuery(this).prop("required")) {
                if (!jQuery(this).val()) {
                    valid = false;
                    var label = jQuery(this).closest(".form-group").find("label").text();
                    error_msg += "* " + label + " is required\n";
                }
            }
        });
        
        if (jQuery("#terms_of_use").prop("checked") == false) {
            valid = false;
            error_msg += "* You need to accept The Terms and Conditions.\n";
        }
        
        if (valid) {
            jQuery("#" + form_id).submit();	
        } else {
            error_msg = "Please correct the following errors and try again:\n" + error_msg;
            alert(error_msg);
        }		
    }
    </script>
</body>
</html>';

        return $html;
    }
    
    /**
     * Get Contact Form Output
     *
     * @param array $request
     *
     * @return string
     */
    protected function getContactFormOutput($request)
    {
        $ticket_info = $request['ticket_info'];
        $shop_info = $request['shop_info'];
        $platform = $shop_info['platform'];
        
        $platform = $shop_info['platform'];
        $merchant_email = filter_var(trim($ticket_info['email']), FILTER_VALIDATE_EMAIL);
        $shop_url = $shop_info['url'];
        
        $shop_info_html = '<ul>';
        foreach ($shop_info as $key => $value) {
            $label = strtoupper(str_replace('_', ' ', $key));
            if ($key == 'debug_data') {
                $shop_info_html .= '<li><strong>'.$label.'</strong><br/>
                <div style="background-color: #FFFFCC;">'.htmlentities($value, ENT_QUOTES, "UTF-8").'</div></li>';
            } elseif ($key == 'error_data') {
                $shop_info_html .= '<li><strong>'.$label.'</strong><br/>
                <div style="background-color: #FFCCCC;">'.htmlentities($value, ENT_QUOTES, "UTF-8").'</div>
                </li>';
            } else {
                $shop_info_html .= '<li><strong>'.$label.'</strong>: '.$value.'</li>';
            }
        }
        $shop_info_html .= '</ul>';
                
        $email_from = self::VIABILL_TECH_SUPPORT_EMAIL;
        $email_subject = "New ".ucfirst($platform)." Support Request from ".$shop_url;
        $email_body = "Dear support,\n<br/>You have received a new support request with the following details:\n";
        $email_body .= "<h3>Ticket</h3>";
        $email_body .= "<table>";
        $email_body .= "<tr><td style='background: #eee;'><strong>Name:</strong></td><td>".
            $ticket_info['name']."</td></tr>";
        $email_body .= "<tr><td style='background: #eee;'><strong>Email:</strong></td><td>".
            $ticket_info['email']."</td></tr>";
        $email_body .= "<tr><td style='background: #eee;'><strong>Issue:</strong></td><td>".
            $ticket_info['issue']."</td></tr>";
        $email_body .= "</table>";
        $email_body .= "<h3>Shop Info</h3>";
        $email_body .= $shop_info_html;
        
        $logo_img = $this->repository->getUrl('Viabillhq_Payment::images/ViaBill_Logo.png');
                
        $sender_email = $this->getSenderEmail($request);
        $to = self::VIABILL_TECH_SUPPORT_EMAIL;
        $merchant_email = $ticket_info['email'];
        $support_email = self::VIABILL_TECH_SUPPORT_EMAIL;
            
        $headers = "From: " . $sender_email . "\r\n";
        $headers .= "Reply-To: ". $merchant_email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                
        if (mail($to, $email_subject, $email_body, $headers)) { // @codingStandardsIgnoreLine
            $body = "<div style='background-color: #CEC9FF; padding: 15px; margin:5%;
             border:3px dashed #5a00ff; text-align: center;'><h3>Success!</h3>
             Your request has been received successfully! We will get back to you soon at
              <strong>$merchant_email</strong>. You may also contact us at <strong>$support_email</strong></div>";
        } else {
            $body = "<div style='background-color: #FFEEEE; padding: 15px; margin:5%;
             border:1px solid #030303; text-align: center;'><h3>Error</h3>Could not email your request form to 
             the technical support team. Please try again or contact us at <strong>$support_email</strong></div>";
        }
        
        $html = '<!DOCTYPE html>
	<html lang="en-US" prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
             crossorigin="anonymous">
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
             crossorigin="anonymous"></script>	
            <script src="https://code.jquery.com/jquery-2.2.4.min.js"
             crossorigin="anonymous"></script>
		</head>
        <body style="background-color: #F5F2FF;">
        <div style="text-align: center; margin-top:25px;">
            <a href="https://viabill.com/dk/" rel="home" aria-label="ViaBill">
                <svg style="display: block; margin: auto; width: 176px; height: 37px;"
                 preserveAspectRatio="xMinYMin meet" xmlns="http://www.w3.org/2000/svg">
                 <path d="M30.077.001a4.105 4.105 0 0 0-3.835 2.643l-9.15 24.013L7.936 2.642A4.1
                  4.1 0 1 0 .318 5.675l11.093 26.706a6.178 6.178 0 0 0 5.7 3.809 6.176 6.176 0 0 0
                   5.707-3.815l11.053-26.7A4.1 4.1 0 0 0 30.077.001zm17.318 0a3.661 3.661 0 0 0-3.38
                    2.248l-12.09 28.858a3.664 3.664 0 0 0 6.758 2.832l12.09-28.858a3.664 3.664
                     0 0 0-3.378-5.08zm69.4 0a3.974 3.974 0 0 0-3.669 2.451 3.982 3.982 0 0 0-.302
                      1.52v28.242a3.972 3.972 0 0 0 7.944 0V3.974a3.972 3.972 0 0 0-3.973-3.973zm28.16
                       28.707h-9.424V4a4 4 0 1 0-8 0v28.369a3.664 3.664 0 0 0 3.666 3.666h13.758a3.663
                        3.663 0 1 0 0-7.327zm26.7 0h-9.423V4a4 4 0 1 0-8 0v28.369a3.668 3.668 0 0 0
                         3.666 3.666h13.752a3.665 3.665 0 0 0 1.407-7.048 3.657 3.657 0 0 0-1.402-.279zM71.291
                          3.865A6.176 6.176 0 0 0 62.16 1.1a6.176 6.176 0 0 0-2.274 2.773l-11.045 26.7a4.1
                           4.1 0 0 0 3.79 5.674 4.105 4.105 0 0 0 3.836-2.643l9.15-24.012 9.156 24.012a4.1
                            4.1 0 1 0 7.618-3.033L71.29 3.865zm36.018 20.81a9.93 9.93 0 0 0-6.053-8.924
                             7.618 7.618 0 0 0 2.6-5.97c0-6.691-5.661-9.779-12.507-9.779h-9.467a3.475 3.475
                              0 1 0 0 6.949h9.467c3.037 0 4.58 1.545 4.58 3.655s-1.543 3.4-4.683
                               3.4h-3.837a3.347 3.347 0 1 0 .125 6.691h7.276c1.91 0 4.767 1.026 4.767
                                4.5 0 3.355-4.074 4.2-6.145 4.256a3.385 3.385 0 0 0-.653 6.726c.766.143
                                 1.542.222 2.32.237a12.835 12.835 0 0 0 6.701-1.663 11.02 11.02 0 0 0
                                  5.509-10.078z"></path></svg>
            </a>															        
        </div>
		'.$body.'
		</body>
    </html>';

        return $html;
    }
    
    /**
     * Get Request Data
     *
     * @return array
     */
    protected function getRequestedData()
    {
        $request = $this->request->getPost();
        if (isset($request['ticket_info'])) {
            $request['output'] = 1;
        } else {
            $request['output'] = 0;
        }
        return $request;
    }
    
    /**
     * Get Action URL
     *
     * @return string
     */
    protected function getActionURL()
    {
        $url = $this->urlProvider->getUrl('viabill/account/contactsupport');
        return $url;
    }
    
    /**
     * Get Sender email
     *
     * @param array $request
     *
     * @return string
     */
    protected function getSenderEmail($request)
    {
        $senderEmail = '';
        
        $site_host = $this->storeManager->getStore()->getBaseUrl();
        
        $merchant_email = '';
        if (isset($request['ticket_info'])) {
            $ticket_info = $request['ticket_info'];
            if (isset($ticket_info['email'])) {
                $merchant_email = filter_var(trim($ticket_info['email']), FILTER_VALIDATE_EMAIL);
            }
        }
        
        // check if merchant email shares the same domain with the site host
        if (!empty($merchant_email)) {
            list($account, $domain) = explode('@', $merchant_email, 2);
            if (strpos($site_host, $domain)!== false) {
                $senderEmail = $merchant_email;
            }
        }
        
        if (empty($senderEmail)) {
            $senderEmail = $this->scopeConfig->getValue(
                self::CONFIG_PATH_VIABILL_ACCOUNT_EMAIL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        
        # sanity check
        if (empty($senderEmail)) {
            $domain_name = $site_host;

            if (strpos($site_host, '/')!==false) {
                $parts = explode('/', $site_host);
                foreach ($parts as $part) {
                    if (strpos($part, '.')!==false) {
                        $domain_name = $part;
                        break;
                    }
                }
            }

            $parts = explode('.', $domain_name);
            $parts_n = count($parts);
            $sep = '';
            $senderEmail = 'reply@';
            for ($i=($parts_n-2); $i<$parts_n; $i++) {
                $senderEmail .= $sep . $parts[$i];
                $sep = '.';
            }
        }
                    
        return $senderEmail;
    }
    
    /**
     * Get form key
     *
     * @return string
     */
    protected function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get file tail
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
}
