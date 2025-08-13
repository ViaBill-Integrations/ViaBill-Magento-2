<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Exception;

class MessagesMap
{
    /**
     * @var array
     */
    private $messages;

    /**
     * MessagesMap constructor.
     *
     * @param array $messages
     */
    public function __construct(
        array $messages = []
    ) {
        $this->messages = $messages;
    }

    /**
     * Get the message
     *
     * @param string $commandCode
     * @param int $httpResponseCode
     *
     * @return null
     */
    public function getMessage($commandCode, $httpResponseCode)
    {
        if (empty($this->messages)) {
            $this->loadDefaultMessages();
        }
        return $this->messages[$commandCode][$httpResponseCode] ?? null;
    }

    /**
     * Load default error messages.
     */
    private function loadDefaultMessages()
    {
        $this->messages = [
        'capture' => [
        400 =>
        __('Error when trying to capture the order - please contact Viabill for more information.'),
        403 =>
        __('Capture is not longer possible for this transaction - please contact Viabill for more information.'),
        409 =>
        __('You are not allowed to make several capture attempts in a very short time - 
    please try again in 15 minutes or contact Viabill for more info.'),
        500 =>
        __('Error when trying to capture the transaction - please try again in 15 minutes.')
        ],
        'refund' => [
        400 =>
        __('Error when trying to refund the transaction - please contact Viabill for more information.'),
        403 => __('Refund is not possible at the moment - please contact Viabill for more information.'),
        500 => __('Error when trying to refund the transaction - please try again in 15 minutes.')
        ],
        'cancel' => [
        400 => __('Error when trying to cancel the transaction - please contact Viabill for more information.'),
        500 => __('It\'s not possible to cancel the transaction - please try again in 15 minutes.')
        ],
        'renew' => [
        400 => __('It\'s not possible to renew the order at the moment - please contact Viabill for more information.'),
        403 => __('Renew is no longer possible for this transaction.'),
        500 => __('Error when trying to renew the order - please try again in 15 minutes.')
        ]
        ];
    }
}
