<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriter;
use Magento\Framework\Encryption\EncryptorInterface;

class AccountConfiguration
{
    public const CONFIG_PATH_VIABILL_ACCOUNT_EMAIL = 'payment/viabill_account/email';
    public const CONFIG_PATH_VIABILL_ACCOUNT_COUNTRY = 'payment/viabill_account/country';
    public const CONFIG_PATH_VIABILL_ACCOUNT_SHOP_URL = 'payment/viabill_account/shop_url';
    public const CONFIG_PATH_VIABILL_ACCOUNT_CONTACT_NAME = 'payment/viabill_account/contact_name';
    public const CONFIG_PATH_VIABILL_ACCOUNT_PHONE = 'payment/viabill_account/phone';
    public const CONFIG_PATH_VIABILL_ACCOUNT_MERCHANT_NAME = 'payment/viabill_account/merchant_name';
    public const CONFIG_PATH_VIABILL_API_KEY = 'payment/viabill/apikey';
    public const CONFIG_PATH_VIABILL_SECRET = 'payment/viabill/secret';
    public const CONFIG_PATH_VIABILL_PRICE_TAG_SCRIPT = 'payment/viabill/price_tag_script';

    /**
     * @var array
     */
    private $pathMapping = [
        'email' => self::CONFIG_PATH_VIABILL_ACCOUNT_EMAIL,
        'login_email' => self::CONFIG_PATH_VIABILL_ACCOUNT_EMAIL,
        'country' => self::CONFIG_PATH_VIABILL_ACCOUNT_COUNTRY,
        'shop_url' => self::CONFIG_PATH_VIABILL_ACCOUNT_SHOP_URL,
        'url' => self::CONFIG_PATH_VIABILL_ACCOUNT_SHOP_URL,
        'contact_name' => self::CONFIG_PATH_VIABILL_ACCOUNT_CONTACT_NAME,
        'name' => self::CONFIG_PATH_VIABILL_ACCOUNT_CONTACT_NAME,
        'phone' => self::CONFIG_PATH_VIABILL_ACCOUNT_PHONE,
        'merchant_name' => self::CONFIG_PATH_VIABILL_ACCOUNT_MERCHANT_NAME,
        'key' => self::CONFIG_PATH_VIABILL_API_KEY,
        'secret' => self::CONFIG_PATH_VIABILL_SECRET,
        'pricetagScript' => self::CONFIG_PATH_VIABILL_PRICE_TAG_SCRIPT,
    ];

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * AccountConfiguration constructor.
     *
     * @param ConfigWriter $configWriter
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ConfigWriter $configWriter,
        EncryptorInterface $encryptor
    ) {
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
    }

    /**
     * Save configuration
     *
     * @param array $configuration
     */
    public function save($configuration = [])
    {
        foreach ($configuration as $confName => $confValue) {
            $confPath = $this->getPath($confName);
            if ($confPath === null) {
                continue;
            }
            if ($this->isEncryptionRequired($confPath)) {
                $confValue = $this->encryptor->encrypt($confValue);
            }
            $this->saveConfig($confPath, $confValue);
        }
    }

    /**
     * Save configuration
     *
     * @param string $path
     * @param string $value
     */
    private function saveConfig($path, $value)
    {
        $this->configWriter->save(
            $path,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
    }

    /**
     * Get path
     *
     * @param string $path
     *
     * @return string|null
     */
    private function getPath($path)
    {
        return $this->pathMapping[$path] ?? null;
    }

    /**
     * Check if encryption is required
     *
     * @param string $path
     *
     * @return bool
     */
    private function isEncryptionRequired($path)
    {
        return \in_array($path, [
            self::CONFIG_PATH_VIABILL_API_KEY,
            self::CONFIG_PATH_VIABILL_SECRET
        ], true);
    }
}
