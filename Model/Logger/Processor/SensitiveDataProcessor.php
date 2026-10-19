<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Logger\Processor;

use Magento\Payment\Gateway\ConfigInterface;
use Monolog\LogRecord;

/**
 * Masks the ViaBill API key, API secret and request signatures in every ViaBill log record.
 */
class SensitiveDataProcessor
{
    /**
     * Field names whose values are masked in JSON bodies and URL query strings.
     */
    private const SENSITIVE_FIELDS = 'apikey|key|secret|signature|sha256check';

    private const MASK = '***';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * SensitiveDataProcessor constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Mask the message of a log record (Monolog 3 LogRecord or Monolog 1/2 array)
     *
     * @param LogRecord|array $record
     * @return LogRecord|array
     */
    public function __invoke($record)
    {
        if ($record instanceof LogRecord) {
            return new LogRecord(
                $record->datetime,
                $record->channel,
                $record->level,
                $this->mask((string) $record->message),
                $record->context,
                $record->extra,
                $record->formatted
            );
        }
        if (is_array($record) && isset($record['message'])) {
            $record['message'] = $this->mask((string) $record['message']);
        }
        return $record;
    }

    /**
     * Mask the configured credentials and all sensitive fields in a message
     *
     * @param string $message
     * @return string
     */
    public function mask(string $message): string
    {
        foreach ($this->getCredentials() as $credential) {
            $message = str_replace($credential, self::MASK, $message);
        }

        $fields = self::SENSITIVE_FIELDS;
        $masked = preg_replace(
            [
                // JSON: "apikey":"value"
                '/("(?:' . $fields . ')"\s*:\s*")[^"]*(")/i',
                // URL query string: ?key=value&signature=value
                '/([?&](?:' . $fields . ')=)[^&\s"\]]*/i',
            ],
            ['$1' . self::MASK . '$2', '$1' . self::MASK],
            $message
        );
        return $masked ?? $message;
    }

    /**
     * Get the configured API key and API secret
     *
     * @return string[]
     */
    private function getCredentials(): array
    {
        $credentials = [];
        try {
            foreach (['secret', 'apikey'] as $field) {
                $value = (string) $this->config->getValue($field);
                // Very short values would mask unrelated text.
                if (strlen($value) >= 8) {
                    $credentials[] = $value;
                }
            }
        } catch (\Throwable $e) { // @codingStandardsIgnoreLine
            // Never fail logging because the configuration is not available.
        }
        return $credentials;
    }
}
