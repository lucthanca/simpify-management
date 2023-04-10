<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Helper;

trait UtilTrait
{
    /**
     * HMAC creation helper.
     *
     * @param array  $opts   The options for building the HMAC.
     * @param string $secret The app secret key.
     *
     * @return string
     */
    public function createHmac(array $opts, string $secret): string
    {
        // Setup defaults
        $data = $opts['data'];
        $raw = $opts['raw'] ?? false;
        $buildQuery = $opts['buildQuery'] ?? false;
        $buildQueryWithJoin = $opts['buildQueryWithJoin'] ?? false;
        $encode = $opts['encode'] ?? false;

        if ($buildQuery) {
            //Query params must be sorted and compiled
            ksort($data);
            $queryCompiled = [];
            foreach ($data as $key => $value) {
                $queryCompiled[] = "{$key}=".(is_array($value) ? implode(',', $value) : $value);
            }
            $data = implode(
                $buildQueryWithJoin ? '&' : '',
                $queryCompiled
            );
        }

        // Create the hmac all based on the secret
        $hmac = hash_hmac('sha256', $data, $secret, $raw);

        // Return based on options
        return $encode ? base64_encode($hmac) : $hmac;
    }

    /**
     * URL-safe Base64 decoding.
     *
     * Replaces `-` with `+` and `_` with `/`.
     *
     * Adds padding `=` if needed.
     *
     * @param string $data The data to be decoded.
     *
     * @return string
     */
    public function base64UrlDecode($data)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
        // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
    }

    /**
     * URL-safe Base64 encoding.
     *
     * Replaces `+` with `-` and `/` with `_` and trims padding `=`.
     *
     * @param string $data The data to be encoded.
     *
     * @return string
     */
    public function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
