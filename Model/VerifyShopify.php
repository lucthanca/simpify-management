<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\App\RequestInterface as IRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SimiCart\SimpifyManagement\Api\ShopRepositoryInterface as IShopRepository;
use \SimiCart\SimpifyManagement\Exceptions\SignatureVerificationException;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;
use SimiCart\SimpifyManagement\Model\Session as ShopSession;
use SimiCart\SimpifyManagement\Registry\CurrentShop;

class VerifyShopify
{
    private SessionTokenFactory $sessionTokenFactory;
    private ShopSession $shopSession;
    private ConfigProvider $configProvider;
    private IShopRepository $shopRepository;
    private CurrentShop $currentShop;
    private \Magento\Customer\Model\Session $customerSession;

    /**
     * @param SessionTokenFactory $sessionTokenFactory
     * @param ShopSession $shopSession
     * @param ConfigProvider $configProvider
     * @param IShopRepository $shopRepository
     * @param CurrentShop $currentShop
     */
    public function __construct(
        SessionTokenFactory $sessionTokenFactory,
        ShopSession $shopSession,
        ConfigProvider $configProvider,
        IShopRepository $shopRepository,
        CurrentShop $currentShop,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->sessionTokenFactory = $sessionTokenFactory;
        $this->shopSession = $shopSession;
        $this->configProvider = $configProvider;
        $this->shopRepository = $shopRepository;
        $this->currentShop = $currentShop;
        $this->customerSession = $customerSession;
    }

    /**
     * Verify the shopify request
     *
     * @param IShop $shop
     * @param IRequest $request
     * @return array
     * @throws SignatureVerificationException|LocalizedException
     */
    public function execute(IRequest $request)
    {
        $hmacResult = $this->verifyHmac($request);
        if ($hmacResult === false) {
            // Invalid HMAC
            throw new SignatureVerificationException(__('Unable to verify signature.'));
        }

        //. Skip authenticate route
        if ($request->getFullActionName() === 'simpify_authenticate_index') {
            return ['skip', null];
        }

        $tokenSource = $request->getParam('token');
        if (!$tokenSource) {
            $shop = $this->loadShop($request->getParam('shop'));
            $shopHasInstalledPrevious = $shop->getId() && $shop->hasOfflineAccess() && !$shop->hasUninstalled();
            return $shopHasInstalledPrevious ?
                $this->handleMissingToken($request, $shop) :
                $this->handleInvalidShop($request->getParam('shop'));
        }

        $token = $this->sessionTokenFactory->create(['token' => $tokenSource]);
        $shop = $this->loadShop($token->getShopDomain());
        if (!$shop->getId()) {
            throw new NoSuchEntityException(__('No shop provided!'));
        }
//        $this->shopSession->loginById((int) $shop->getId());
//        vadu_html($token->getSessionId());
        return ['logged_in', ['shop' => $shop->getShopDomain(), 'host' => $request->getParam('host'), 'session' => $token->getSessionId()]];
    }

    /**
     * Load shop by domain
     *
     * @param string|null $domain
     * @return IShop
     * @throws NoSuchEntityException
     */
    protected function loadShop(?string $domain): IShop
    {
        return $this->shopRepository->getByDomain($domain);
    }

    /**
     * Handle missing token | forward to token controller
     *
     * @param IRequest $request
     * @param IShop $shop
     * @return array
     */
    protected function handleMissingToken(IRequest $request, Ishop $shop): array
    {
        $params = $request->getParams();
        // At this point the HMAC and other details are verified already, filter it out
        $filteredParams = array_filter($params, function ($param) {
            return !in_array($param, ['hmac', 'locale', 'new_design_language', 'timestamp', 'session', 'shop']);
        }, ARRAY_FILTER_USE_KEY);

        /**
         * Get the current path info for the request.
         *
         * @return string
         */
        $getUrlPath = function () use ($request) {
            $pattern = trim($request->getPathInfo(), '/');
            return $pattern === '' ? '/' : $pattern;
        };

        /**
         * Begin a string with a single instance of a given value.
         *
         * @param  string  $value
         * @param  string  $prefix
         * @return string
         */
        $strStart = function (string $value, string $prefix) {
            $quoted = preg_quote($prefix, '/');

            return $prefix.preg_replace('/^(?:'.$quoted.')+/u', '', $value);
        };

        $path = $getUrlPath();
        $target = $strStart($path, '/');
        if (!empty($filteredParams)) {
            $target .= '?'.http_build_query($filteredParams);
        }

        return ['token_missing', ['shop' => $shop->getShopDomain(), 'target' => $target, 'host' => $request->getParam('host')]];
    }

    /**
     * Show new shop installation
     *
     * @param string $shop
     * @return array
     */
    protected function handleInvalidShop(string $shop)
    {
        $this->initShop($shop);
        return ['new_shop', null];
    }

    /**
     * Get shop or create new shop instance by provided request param
     *
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initShop($shopDomain)
    {
        $shop = $this->shopRepository->getByDomain($shopDomain);
        if (!$shop->getId()) {
            $shopData = [
                IShop::SHOP_NAME => $shopDomain,
                IShop::SHOP_DOMAIN => $shopDomain,
                IShop::SHOP_EMAIL => "shop@$shopDomain",
            ];

            $this->shopRepository->createShop($shopData);
            $shop = $this->shopRepository->getByDomain($shopDomain);
        }
        $this->currentShop->set($shop);
    }

    /**
     * Verify HMAC data, if present.
     *
     * @param IShop $shop
     * @param IRequest $request The request object.
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function verifyHmac(IRequest $request): ?bool
    {
        $hmac = $this->getHmacFromRequest($request);
        if ($hmac['source'] === null) {
            // No HMAC, skip
            return null;
        }
        // We have HMAC, validate it
        $data = $this->getRequestData($request, $hmac['source']);
        return $this->verifyRequest($data);
    }

    /**
     * Verify request HMAC
     *
     * @param array $params
     * @return bool
     * @throws LocalizedException
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
     * Grab the request data.
     *
     * @param IRequest $request The request object.
     * @param string  $source  The source of the data.
     *
     * @return array
     */
    protected function getRequestData(IRequest $request, string $source): array
    {
        // All possible methods
        $options = [
            // GET/POST
            'input' => function () use ($request): array {
                // Verify
                $verify = [];
                foreach ($request->getParams() as $key => $value) {
                    $verify[$key] = $this->parseDataSourceValue($value);
                }

                return $verify;
            },
            // Headers
            'header' => function () use ($request): array {
                // Always present
                $shop = $request->getHeader('X-Shop-Domain');
                $signature = $request->getHeader('X-Shop-Signature');
                $timestamp = $request->getHeader('X-Shop-Time');

                $verify = [
                    'shop' => $shop,
                    'hmac' => $signature,
                    'timestamp' => $timestamp,
                ];

                // Sometimes present
                $code = $request->getHeader('X-Shop-Code');
                $locale = $request->getHeader('X-Shop-Locale');
                $state = $request->getHeader('X-Shop-State');
                $id = $request->getHeader('X-Shop-ID');
                $ids = $request->getHeader('X-Shop-IDs');

                foreach (compact('code', 'locale', 'state', 'id', 'ids') as $key => $value) {
                    if ($value) {
                        $verify[$key] = $this->parseDataSourceValue($value);
                    }
                }

                return $verify;
            },
            // Headers: Referer
            'referer' => function () use ($request): array {
                $url = parse_url($request->getHeader('referer'), PHP_URL_QUERY);
                if (!$url) {
                    return [];
                }
                parse_str($url, $refererQueryParams);

                // Verify
                $verify = [];
                foreach ($refererQueryParams as $key => $value) {
                    $verify[$key] = $this->parseDataSourceValue($value);
                }

                return $verify;
            },
        ];

        return $options[$source]();
    }

    /**
     * Parse the data source value.
     * Handle simple key/values, arrays, and nested arrays.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function parseDataSourceValue($value): string
    {
        /**
         * Format the value.
         *
         * @param mixed $val
         *
         * @return string
         */
        $formatValue = function ($val): string {
            return is_array($val) ? '["'.implode('", "', $val).'"]' : $val;
        };

        // Nested array
        if (is_array($value) && is_array(current($value))) {
            return implode(', ', array_map($formatValue, $value));
        }

        // Array or basic value
        return $formatValue($value);
    }

    /**
     * Grab the HMAC value, if present, and how it was found.
     * Order of precedence is:.
     *
     *  - GET/POST Variable
     *  - Headers
     *  - Referer
     *
     * @param IRequest $request The request object.
     *
     * @return array
     */
    protected function getHmacFromRequest(IRequest $request): array
    {
        $options = [
            'input' => $request->getParam('hmac'),
            'header' => $request->getHeader('X-Shop-Signature'),
            'referer' => function () use ($request): ?string {
                $url = parse_url($request->getHeader('referer', ''), PHP_URL_QUERY);
                parse_str($url ?? '', $refererQueryParams);
                if (! $refererQueryParams || ! isset($refererQueryParams['hmac'])) {
                    return null;
                }

                return $refererQueryParams['hmac'];
            },
        ];
        // Loop through each until we find the HMAC
        foreach ($options as $method => $value) {
            $result = is_callable($value) ? $value() : $value;
            if (!empty($result)) {
                return ['source' => $method, 'value' => $value];
            }
        }
        return ['source' => null, 'value' => null];
    }
}
