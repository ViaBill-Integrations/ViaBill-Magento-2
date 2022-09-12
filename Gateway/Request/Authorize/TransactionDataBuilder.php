<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request\Authorize;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Catalog\Helper\Image;
use Viabillhq\Payment\Gateway\Request\SubjectReader;
use Viabillhq\Payment\Gateway\Request\ViabillRequestDataBuilder;
use Viabillhq\Payment\Model\TransactionProvider;

class TransactionDataBuilder extends ViabillRequestDataBuilder
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TransactionProvider
     */
    private $transactionProvider;    

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * TransactionDataBuilder constructor.
     *
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param TransactionProvider $transactionProvider     
     * @param array $requestFields
     */
    public function __construct(
        ConfigInterface $config,
        SubjectReader $subjectReader,
        TransactionProvider $transactionProvider,
        Image $imageHelper,
        array $requestFields
    ) {
        parent::__construct($requestFields);
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->transactionProvider = $transactionProvider;        
        $this->imageHelper = $imageHelper;
        // Note: If you don't want to inject imageHelper in the constructor, 
        // use ObjectManager instead:
        // $objectManager =\Magento\Framework\App\ObjectManager::getInstance();
        // $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
    }

    /**
     * Get order inc id
     *
     * @param array $buildSubject
     *
     * @return mixed
     */
    protected function getOrderIncrementId(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        return $order->getIncrementId();
    }

    /**
     * Get currency
     *
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getCurrency(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        return $order->getOrderCurrencyCode();
    }

    /**
     * Get amount
     *
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getAmount(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        return (string)round($order->getGrandTotal(), 2);
    }

    /**
     * Get authorize transaction id
     *
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getAuthTransactionId(array $buildSubject)
    {
        $orderId = $this->getOrderIncrementId($buildSubject);
        return $this->transactionProvider->generateViabillTransactionId($orderId);
    }

    /**
     * Get customer info
     *
     * @param array $buildSubject
     * @param string $key
     *
     * @return array
     */
    protected function getCustomerInfo(array $buildSubject, $key = null)
    {
        $info = [
            'email'=>'',
            'phoneNumber'=>'',
            'firstName'=>'',
            'lastName'=>'',
            'fullName'=>'',
            'address'=>'',
            'city'=>'',
            'postalCode'=>'',
            'country'=>''
        ];
          
        $order = $this->subjectReader->readOrder($buildSubject);
        if (!empty($order)) {
            try {
                $firstname = $order->getCustomerFirstname();
                $lastname = $order->getCustomerLastname();
                $fullname = trim($firstname.' '.$lastname);
                if (!empty($fullname)) {
                    $info['firstName'] = $firstname;
                    $info['lastName'] = $lastname;
                    $info['fullName'] = $fullname;
                }
                $email = $order->getEmail();
                if (!empty($email)) {
                    $info['email'] = $email;
                }

                $address = null;
                $billingAddress = $order->getBillingAddress();
                if (!empty($billingAddress)) {
                    $address = $billingAddress;
                } else {
                    $shippingAddress = $order->getShippingAddress();
                    if (!empty($shippingAddress)) {
                        $address = $shippingAddress;
                    }
                }
                if (isset($address)) {
                    if (empty($info['email'])) {
                        $email = $address->getEmail();
                        if (!empty($email)) {
                            $info['email'] = $email;
                        }
                    }
                    $phone = $address->getTelephone();
                    if (!empty($phone)) {
                        $info['phoneNumber'] = $this->sanitizePhone($phone, $address->getCountryId());
                    }
                    $city = $address->getCity();
                    if (!empty($city)) {
                        $info['city'] = $city;
                    }
                    $postal_code = $address->getPostcode();
                    if (!empty($postal_code)) {
                        $info['postalCode'] = $postal_code;
                    }
                    $street = $address->getStreet();
                    if (!empty($street)) {
                        $street_str = '';
                        if (is_array($street)) {
                            $street_str = implode(' ', $street);
                        } else {
                            $street_str = $street;
                        }
                        $info['address'] = $street_str;
                    }
                    $country = $address->getCountryId();
                    if (!empty($country)) {
                        $info['country'] = $country;
                    }
                }
            } catch (\Exception $e) {
                // do nothing
                $error_msg = $e->getMessage();
            }
        }

        if (!empty($key)) {
            if (isset($info[$key])) {
                return $info[$key];
            }
        }

        return $info;
    }

    /**
     * Get cart info
     *
     * @param array $buildSubject
     * @param string $key
     *
     * @return array
     */
    protected function getCartInfo(array $buildSubject, $key = null)
    {                          
        $order = $this->subjectReader->readOrder($buildSubject);
                
        // sanity check
        if (empty($order)) {
            return null;
        }

        $error_msg = null;

        $info = [
            'date_created' => '',
            'subtotal'=> '',
            'tax' => '',
            'shipping'=> '',
            'discount'=> '',
            'total'=> '',
            'currency'=> '',
            'quantity'=> '',
            'billing_email' => '',
            'billing_phone' => '',
            'shipping_city' => '',
            'shipping_postcode' => '',
            'shipping_country' => '',
            'shipping_same_as_billing' => ''
        ];

        try {
            $info['date_created'] = $order->getCreatedAt();
            $info['subtotal'] = $order->getSubtotal();
            $info['tax'] = $order->getTaxAmount();
            $info['shipping'] = $order->getShippingAmount();
            $info['discount'] = $order->getDiscountAmount();
            $info['total'] = $order->getTotalDue();
            $info['currency'] = $order->getOrderCurrencyCode();
            $info['quantity'] = $order->getTotalItemCount();
            
            $billing_email = '';
            $billing_phone = '';
            $billing_street = '';
            $billing_city = '';
            $billing_postcode = '';
            $billing_country = '';
            $shipping_street = '';
            $shipping_city = '';
            $shipping_postcode = '';
            $shipping_country = '';
            
            $email = $order->getEmail();
            if (!empty($email)) {
                $billing_email = $email;
            }
            
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            
            if (isset($billingAddress)) {
                if (empty($billing_email)) {
                    $billing_email = $billingAddress->getEmail();                   
                }
                $phone = $billingAddress->getTelephone();
                if (!empty($phone)) {
                    $billing_phone = $this->sanitizePhone($phone, $billingAddress->getCountryId());
                }
                $city = $billingAddress->getCity();
                if (!empty($city)) {
                    $billing_city = $city;
                }
                $postal_code = $billingAddress->getPostcode();
                if (!empty($postal_code)) {
                    $billing_postcode = $postal_code;
                }
                $street = $billingAddress->getStreet();
                if (!empty($street)) {
                    $street_str = '';
                    if (is_array($street)) {
                        $street_str = implode(' ', $street);
                    } else {
                        $street_str = $street;
                    }
                    $billing_street = trim($street_str);
                }
                $country = $billingAddress->getCountryId();
                if (!empty($country)) {
                    $billing_country = $country;
                }
            }

            if (isset($shippingAddress)) {
                if (empty($billing_email)) {
                    $billing_email = $shippingAddress->getEmail();                    
                }
                $phone = $shippingAddress->getTelephone();
                if (!empty($phone)) {
                    if (empty($billing_phone)) {
                        $billing_phone = $phone;
                    }                    
                }
                $city = $shippingAddress->getCity();
                if (!empty($city)) {
                    $shipping_city = $city;
                }
                $postal_code = $shippingAddress->getPostcode();
                if (!empty($postal_code)) {
                    $shipping_postcode = $postal_code;
                }
                $street = $shippingAddress->getStreet();
                if (!empty($street)) {
                    $street_str = '';
                    if (is_array($street)) {
                        $street_str = implode(' ', $street);
                    } else {
                        $street_str = $street;
                    }
                    $shipping_street = trim($street_str);
                }
                $country = $shippingAddress->getCountryId();
                if (!empty($country)) {
                    $shipping_country = $country;
                }
            }

            $shipping_same_as_billing = 'yes';
            $compare_addresses = [
                'street' => [$billing_street, $shipping_street],
                'city' => [$billing_city, $shipping_city],
                'postcode' => [$billing_postcode, $shipping_postcode],
                'country' => [$billing_country, $shipping_country]
            ];
            foreach ($compare_addresses as $c_values) {
                $b_value = $c_values[0];
                $s_value = $c_values[1];
                if (!empty($b_value) && !empty($s_value)) {
                    if ($b_value != $s_value) {
                        $shipping_same_as_billing = 'no';
                    }
                }
            }

            $info['billing_email'] = $billing_email;
            $info['billing_phone'] = $billing_phone;
            $info['shipping_city'] = $shipping_city;
            $info['shipping_postcode'] = $shipping_postcode;
            $info['shipping_country'] = $shipping_country;
            $info['shipping_same_as_billing'] = $shipping_same_as_billing;            

            $orderAllItems = $order->getAllItems();
            if ($orderAllItems) {
                $products = [];
                foreach ($orderAllItems as $item) {
                    $product = $item->getProduct();

                    $product_price = (float) $item->getPrice();
                    $product_quantity = (int) $item->getQtyOrdered();
                    $product_tax = (float) $item->getTaxAmount();

                    $product_entry = [
                        'name' => $product->getName(),    
                        'quantity' => $product_quantity,
                        'subtotal' => number_format($product_price * $product_quantity, 2),
                        'tax' => $product_tax
                    ];

                    /*
                    $product_options = $product->getOptions();
                    if (!empty($product_options)) {
                        $product_entry['meta'] = '';
                    }
                    */

                    if ($product->getIsVirtual()) {
                        $product_entry['virtual'] = 1;
                    }

                    $product_url = $product->getProductUrl();
                    if (!empty($product_url)) {
                        $product_entry['product_url'] = $product_url;
                    }

                    $image_url = null;
                    $image_sizes = [                        
                        'product_base_image',
                        'product_small_image',
                        'product_thumbnail_image'
                    ];                           
                    foreach ($image_sizes as $image_size) {
                        if (isset($image_url)) continue;                        
                        $image_url = $this->imageHelper->init($product, $image_size)->getUrl();
                        if (!empty($image_url)) {
                            $product_entry['image_url'] = $image_url;                 
                        }                        
                    }

                    // $product_entry['description'] = $this->truncateDescription($product->getDescription());

                    $products[] = $product_entry;                    
                }
                $info['products'] = $products;
            } 
        } catch (\Exception $e) {
            // do nothing
            $error_msg = $e->getMessage();
            return $error_msg;
        }

        if (!empty($key)) {
            if (isset($info[$key])) {
                return $info[$key];
            }
        }        

        return json_encode($info);
    }

    public function sanitizePhone($phone, $country_code = null) {
        if (empty($phone)) {
            return $phone;
        }
        if (empty($country_code)) {
            return $phone;
        }
        $clean_phone = str_replace(array('+','(',')','-',' '),'',$phone);
        if (strlen($clean_phone)<3) {
            return $phone;
        }
        $country_code = strtoupper($country_code);
        switch ($country_code) {
            case 'US':
            case 'USA': // +1
                $prefix = substr($clean_phone, 0, 1);
                if ($prefix == '1') {
                    $phone_number = substr($clean_phone, 1);
                    if (strlen($phone_number)==10) {
                        $phone = $phone_number;
                    }
                }                
                break;
            case 'DK': 
            case 'DNK': // +45
                $prefix = substr($clean_phone, 0, 2);
                if ($prefix == '45') {
                    $phone_number = substr($clean_phone, 2);
                    if (strlen($phone_number)==8) {
                        $phone = $phone_number;
                    }
                }
                break;
            case 'ES': 
            case 'ESP': // +34
                $prefix = substr($clean_phone, 0, 2);
                if ($prefix == '34') {
                    $phone_number = substr($clean_phone, 2);
                    if (strlen($phone_number)==9) {
                        $phone = $phone_number;
                    }
                }
                break;        
        }

        return $phone;
    }

    public function truncateDescription($text, $maxchar=200, $end='...') {
        if (empty($text)) return '';
        $text = strip_tags(trim($text));        
        if (strlen($text) > $maxchar || $text == '') {
            $words = preg_split('/\s/', $text);
            $output = '';
            $i = 0;
            while (1) {
                $length = strlen($output)+strlen($words[$i]);
                if ($length > $maxchar) {
                    break;
                }
                else {
                    $output .= " " . $words[$i];
                    ++$i;
                }
            }
            $output .= $end;
        }
        else {
            $output = $text;
        }
        return $output;
    }
}
