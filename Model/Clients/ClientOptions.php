<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Model\Clients;

use Magento\Framework\DataObject;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;

/**
 * Required options for Client
 *
 * @method string|null getApiKey
 * @method string setApiKey(string $key)
 * @method string|null getApiSecret
 * @method string getApiVersion
 * @method IShop getShop
 * @method callable|null getGuzzleHandler
 * @method bool|null getPrivate
 */
class ClientOptions extends DataObject
{
    /**
     * API version pattern.
     *
     * @var string
     */
    public const VERSION_PATTERN = '/([0-9]{4}-[0-9]{2})|unstable/';

    /**
     * Additional Guzzle options.
     *
     * @var array
     */
    protected $guzzleOptions = [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'timeout' => 10.0,
        'max_retry_attempts' => 2,
        'default_retry_multiplier' => 2.0,
        'retry_on_status' => [429, 503, 500],
    ];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setGuzzleOptions($data['guzzle_options'] ?? []);
        parent::__construct($data);
    }

    /**
     * Set options for Guzzle.
     *
     * @param array $options
     * @return $this
     */
    public function setGuzzleOptions(array $options): self
    {
        $this->guzzleOptions = array_merge($this->guzzleOptions, $options);
        return $this;
    }

    /**
     * Get options for Guzzle.
     *
     * @return array
     */
    public function getGuzzleOptions(): array
    {
        return $this->guzzleOptions;
    }

    /**
     * Determines if the calls are private.
     *
     * Current only support public
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        // Current only support public
        // TODO: use return $this->getPrivate() === true; when implementation both api call type (private and public)
        return false;
    }
}
