<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request\Authorize;

use Magento\Payment\Gateway\ConfigInterface;
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
        array $requestFields
    ) {
        parent::__construct($requestFields);
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->transactionProvider = $transactionProvider;
    }

    /**
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
     * @param array $buildSubject
     * @param string $key
     *
     * @return array
     */
    protected function getCustomerInfo(array $buildSubject, $key = null)
    {
        $info = array(
            'email'=>'',
            'phoneNumber'=>'',
            'firstName'=>'',
            'lastName'=>'',
            'fullName'=>'',
            'address'=>'',
            'city'=>'',
            'postalCode'=>'',
            'country'=>''
        );             
          
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
                        $info['phoneNumber'] = $phone;
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
                exit($e->getMessage());
            }        
        }        
		
		if (!empty($key)) {
			if (isset($info[$key])) return $info[$key];
		}
		
        return $info;
    }
    
}
