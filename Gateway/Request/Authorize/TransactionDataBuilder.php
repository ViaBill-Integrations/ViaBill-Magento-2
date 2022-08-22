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
            'subtotal'=> '',
            'tax' => '',
            'shipping'=> '',
            'discount'=> '',
            'total'=> '',
            'currency'=> '',
            'quantity'=> ''            
        ];

        try {            
            $info['subtotal'] = $order->getSubtotal();
            $info['tax'] = $order->getTaxAmount(); 
            $info['shipping'] = $order->getShippingAmount();
            $info['discount'] = $order->getDiscountAmount();
            $info['total'] = $order->getTotalDue();
            $info['currency'] = $order->getOrderCurrencyCode();
            $info['quantity'] = $order->getTotalItemCount();        

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
                        // 'description' => $item->getDescription(),
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

                    $products[] = $product_entry;                    
                }
                $info['products'] = $products;
            } 
        } catch (\Exception $e) {
            // do nothing
            $error_msg = $e->getMessage();
            return '';
        }

        if (!empty($key)) {
            if (isset($info[$key])) {
                return $info[$key];
            }
        }        

        return json_encode($info);
    }
}
