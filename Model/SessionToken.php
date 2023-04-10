<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model;

use Assert\Assert;
use Assert\AssertionFailedException;
use Magento\Framework\Exception\LocalizedException;
use SimiCart\SimpifyManagement\Helper\UtilTrait;

/**
 * @property mixed $dest
 * @property mixed $iss
 * @property mixed $aud
 * @property false|string[] $parts
 * @property mixed $sid
 * @property mixed $jti
 * @property mixed $sub
 * @property mixed $exp
 * @property mixed $nbf
 * @property mixed $iat
 */
class SessionToken
{
    use UtilTrait;

    /**
     * Message for invalid token.
     *
     * @var string
     */
    public const EXCEPTION_INVALID = 'Session token is invalid.';

    /**
     * Message for expired token.
     *
     * @var string
     */
    public const EXCEPTION_EXPIRED = 'Session token has expired.';

    /**
     * The regex for the format of the JWT.
     *
     * @var string
     */
    public const TOKEN_FORMAT = '/^eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9\.[A-Za-z0-9\-\_=]+\.[A-Za-z0-9\-\_\=]*$/';

    /**
     * Time added to the expiration time, extends the validity period of a session token
     *
     * @var int
     */
    public const LEEWAY_SECONDS = 10;

    protected string $string;

    /**
     * @var ?string
     */
    private $shopDomain;

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    /**
     * Shopify Token Constructor
     *
     * @param ConfigProvider $configProvider
     * @param string $token
     * @param bool $verifyToken
     * @throws AssertionFailedException
     */
    public function __construct(ConfigProvider $configProvider, string $token, bool $verifyToken = true)
    {
        $this->configProvider = $configProvider;
        $this->string = $token;
        $this->decodeToken();

        if ($verifyToken) {
            $this->verifySignature();
            $this->verifyValidity();
            $this->verifyExpiration();
        }
    }

    /**
     * Decode and validate the formatting of the token.
     *
     * @throws LocalizedException If token is malformed.
     *
     * @return void
     */
    protected function decodeToken(): void
    {
        if (!preg_match(self::TOKEN_FORMAT, $this->string)) {
            throw new LocalizedException(__('Session token is malformed.'));
        }
        // Decode the token
        $this->parts = explode('.', $this->string);
        $body = json_decode($this->base64UrlDecode($this->parts[1]), true);
        // Confirm token is not malformed
        foreach ([$body['iss'], $body['dest'], $body['aud'], $body['sub'],
                     $body['exp'], $body['nbf'], $body['iat'], $body['jti'], $body['sid']] as $value) {
            if ($value === null) {
                throw new LocalizedException(__('Session token is malformed.'));
            }
        }
        $this->iss = $body['iss'];
        $this->dest = $body['dest'];
        $this->aud = $body['aud'];
        $this->sub = $body['dest'];
        $this->jti = $body['dest'];
        $this->sid = $body['sid'];
        $this->exp = new \DateTime('@'. $body['exp']);
        $this->nbf = new \DateTime('@'. $body['nbf']);
        $this->iat = new \DateTime('@'. $body['iat']);

        $h = \Laminas\Uri\UriFactory::factory($body['dest']);

        $host = $h->getHost();
        $this->shopDomain = $host;
    }

    /**
     * Checks the validity of the signature sent with the token.
     *
     * @throws AssertionFailedException If signature does not match.
     *
     * @return void
     */
    protected function verifySignature(): void
    {
        // Get the token without the signature present
        $partsCopy = $this->parts;
        $signature = array_pop($partsCopy);
        $tokenWithoutSignature = implode('.', $partsCopy);

        // Create a local HMAC
        $secret = $this->configProvider->getApiSecret();
        $hmac = $this->createHmac(['data' => $tokenWithoutSignature, 'raw' => true], $secret);
        $encodedHmac = $this->base64UrlEncode($hmac);

        Assert::that($signature === $encodedHmac)->true();
    }

    /**
     * Checks the token to ensure the issuer and audience matches.
     *
     * @throws AssertionFailedException If invalid token.
     *
     * @return void
     */
    protected function verifyValidity(): void
    {
        Assert::that($this->iss)->contains($this->dest, self::EXCEPTION_INVALID);
        Assert::that($this->aud)->eq($this->configProvider->getApiKey(), self::EXCEPTION_INVALID);
    }

    /**
     * Checks the token to ensure its not expired.
     *
     * @throws AssertionFailedException If token is expired.
     *
     * @return void
     */
    protected function verifyExpiration(): void
    {
        $now = new \DateTime();
        Assert::thatAll([
            $now > $this->getLeewayExpiration(),
            $now < $this->nbf,
            $now < $this->iat,
        ])->false(self::EXCEPTION_EXPIRED);
    }

    /**
     * Get the extended expiration time with leeway of the token.
     *
     * @return \DateTime
     */
    public function getLeewayExpiration(): \DateTime
    {

        return $this->exp->modify("+" . self::LEEWAY_SECONDS . " seconds");
    }

    /**
     * Get the session ID.
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sid;
    }

    /**
     * Get token shop string
     *
     * @return string|null
     */
    public function getShopDomain(): ?string
    {
        return $this->shopDomain;
    }
}
