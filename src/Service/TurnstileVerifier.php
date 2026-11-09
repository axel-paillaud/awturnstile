<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Axelweb
 */
declare(strict_types=1);

namespace Axelweb\AwTurnstile\Service;

/**
 * Validates a Turnstile token against the Cloudflare siteverify API.
 */
class TurnstileVerifier
{
    private const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** Cloudflare tokens are at most 2048 characters */
    private const MAX_TOKEN_LENGTH = 2048;

    private const TIMEOUT = 5;

    /** @var string */
    private $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Returns false on invalid token or network error.
     */
    public function verify(string $token, string $remoteIp = ''): bool
    {
        if ($token === '' || \strlen($token) > self::MAX_TOKEN_LENGTH) {
            return false;
        }

        $fields = [
            'secret' => $this->secretKey,
            'response' => $token,
        ];
        if ($remoteIp !== '') {
            $fields['remoteip'] = $remoteIp;
        }

        $curl = curl_init(self::SITEVERIFY_URL);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
            CURLOPT_TIMEOUT => self::TIMEOUT,
        ]);
        $body = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($body === false) {
            \PrestaShopLogger::addLog('awturnstile: siteverify request failed: ' . $curlError, 3);

            return false;
        }

        $result = json_decode((string) $body, true);

        return \is_array($result) && ($result['success'] ?? false) === true;
    }
}
