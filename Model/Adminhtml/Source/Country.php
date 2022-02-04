<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Gateway\Command\ViabillCommandPool;

class Country implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    private $options;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Country constructor.
     *
     * @param CommandPoolInterface $commandPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        LoggerInterface $logger
    ) {
        $this->commandPool = $commandPool;
        $this->logger = $logger;
    }

    /**
     * Array of options
     *
     * @param bool $isMultiselect
     *
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        if (!$this->options) {
            $this->loadOptions();
        }

        $options = $this->options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }

    /**
     * Load options
     *
     * Load countries and set them into options.
     */
    private function loadOptions()
    {
        try {
            $this->commandPool->get(ViabillCommandPool::COMMAND_GET_COUNTRIES)->execute([]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Set options
     *
     * @param array $countries
     */
    public function setOptions(array $countries)
    {
        $options = [];
        foreach ($countries as $country) {
            $options[] = [
                'value' => $country['code'],
                'label' => __($country['name'])
            ];
        }
        $this->options = $options;
    }
}
