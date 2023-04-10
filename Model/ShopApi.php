<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface as IUrl;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;
use SimiCart\SimpifyManagement\Api\ShopApiInterface as IShopAPI;
use SimiCart\SimpifyManagement\Exceptions\UnhandledShopApiRequestFailed;
use SimiCart\SimpifyManagement\Model\Clients\RestFactory as FRest;
use SimiCart\SimpifyManagement\Model\Clients\Rest;
use SimiCart\SimpifyManagement\Model\Source\AuthMode;

class ShopApi implements IShopAPI
{
    protected Rest $client;
    protected IUrl $IUrl;

    protected \Magento\Framework\Stdlib\DateTime\DateTime $date;
    protected IShop $shop;
    private ConfigProvider $configProvider;

    /**
     * @param FRest $clientFactory
     * @param IUrl $IUrl
     * @param string|null $shopDomain
     * @param array $options
     */
    public function __construct(
        FRest $clientFactory,
        IUrl $IUrl,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ConfigProvider $configProvider,
        ?string $shopDomain = null,
        array $options = []
    ) {
        $this->date = $date;
        $this->client = $clientFactory->create(['shopDomain' => $shopDomain, 'options' => $options]);
        if (!isset($options['shop'])) {
            throw new \Exception("Shop instance is required when init ShopAPI.");
        }
        $this->shop = $options['shop'];
        $this->IUrl = $IUrl;
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritDoc
     */
    public function buildAuthUrl(int $authMode, string $scopes): string
    {
        // authMode inString
        $mode = AuthMode::toNative($authMode);
        return $this->client->getAuthUrl(
            $scopes,
            $this->IUrl->getUrl('simpify/authenticate', ['secure' => true]),
            strtolower($mode)
        );
    }

    /**
     * @inheirtDoc
     */
    public function getAccessData(string $code): array
    {
        return $this->client->requestAccess($code);
    }

    /**
     * @throws UnhandledShopApiRequestFailed
     */
    public function requestStorefrontToken(): string
    {
        try {
            $body = [
                'json' => [
                    'storefront_access_token' => [
                        'title' => "Generate Storefront Token at: {$this->date->gmtDate('Y-m-d H:i:s P')}"
                    ]
                ]
            ];
            $data = $this->client->request('POST', '/admin/api/{{api_version}}/storefront_access_tokens.json', $body);
            if (isset($data['storefront_access_token'])) {
                return $data['storefront_access_token']['access_token'];
            }

            $errorMessage = __("Something went wrong. Please contact us!");
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
        throw new UnhandledShopApiRequestFailed(__($errorMessage));
    }

    /**
     * @inheritDoc
     */
    public function verifyRequest(array $params): bool
    {
        $apiSecret = $this->configProvider->getApiSecret();
        if (!$apiSecret) {
            throw new LocalizedException(__('API secret is missing'));
        }

        if ((isset($params['shop']) && !empty($params['shop'])) &&
            (isset($params['timestamp']) && !empty($params['timestamp'])) &&
            (isset($params['hmac']) && !empty($params['hmac']))
        ) {
            // Grab the HMAC, remove it from the params, then sort the params for hashing
            $hmac = $params['hmac'];
            unset($params['hmac']);
            if (isset($params['secure'])) {
                unset($params['secure']);
            }
            ksort($params);
            // Encode and hash the params (without HMAC), add the API secret, and compare to the HMAC from params
            return $hmac === hash_hmac(
                'sha256',
                urldecode(http_build_query($params)),
                $apiSecret
            );
        }
        return false;
    }

    /**
     * Request shop data
     *
     * @return array
     * @throws \SimiCart\SimpifyManagement\Exceptions\ShopifyApiCallException
     */
    public function getShopInfo(): array
    {
        $data = $this->client->request('GET', '/admin/api/{{api_version}}/shop.json');
        return $data['shop'] ?? [];
    }
}
