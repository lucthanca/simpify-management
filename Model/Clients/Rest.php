<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Model\Clients;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Client;
use SimiCart\SimpifyManagement\Exceptions\ShopifyApiCallException;
use \SimiCart\SimpifyManagement\Model\Clients\ShopifyClientInterface as IShopifyClient;
use SimiCart\SimpifyManagement\Model\Clients\Stacks\AuthRequestFactory;

class Rest implements IShopifyClient
{
    private ?Uri $baseUri = null;
    private ?string $shopDomain;
    private ClientOptions $options;
    private Client $client;

    /**
     * @param ClientOptionsFactory $clientOptionsF
     * @param AuthRequestFactory $authRequestF
     * @param string|null $shopDomain
     * @param array|null $options
     */
    public function __construct(
        ClientOptionsFactory $clientOptionsF,
        Stacks\AuthRequestFactory $authRequestF,
        ?string $shopDomain = null,
        array $options = []
    ) {
        $this->shopDomain = $shopDomain;
        $this->options = $clientOptionsF->create(['data' => $options]);

        $stack = HandlerStack::create($this->getOptions()->getGuzzleHandler());
        $stack->push($authRequestF->create(['api' => $this]), 'request:auth');
        $this->client = new Client(array_merge(
            ['handler' => $stack],
            $this->getOptions()->getGuzzleOptions()
        ));
    }

    /**
     * Gets the auth URL for Shopify to allow the user to accept the app (for public apps).
     *
     * @param string|array $scopes      The API scopes as a comma seperated string or array.
     * @param string       $redirectUri The valid redirect URI for after acceptance of the permissions.
     *                                  It must match the redirect_uri in your app settings.
     * @param string       $mode        The API access mode, offline or per-user.
     *
     * @throws \Exception For missing API key.
     *
     * @return string Formatted URL.
     */
    public function getAuthUrl($scopes, string $redirectUri, string $mode = 'offline'): string
    {
        if ($this->getOptions()->getApiKey() === null) {
            throw new \Exception('API key is missing');
        }
        if (is_array($scopes)) {
            $scopes = implode(',', $scopes);
        }
        $query = [
            'client_id' => $this->getOptions()->getApiKey(),
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
        ];
        if ($mode !== null && $mode !== 'offline') {
            $query['grant_options'] = [$mode];
        }
        return (string) $this->getBaseUri()
            ->withPath("/admin/oauth/authorize")
            ->withQuery(preg_replace('/%5B\d+%5D/', '%5B%5D', http_build_query($query)));
    }

    /**
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function requestAccess(string $code): array
    {
        if ($this->getOptions()->getApiSecret() === null || $this->getOptions()->getApiKey() === null) {
            // Key and secret required
            throw new \Exception('API key or secret is missing');
        }
        // Do a JSON POST request to grab the access token
        $url = $this->getBaseUri()->withPath('/admin/oauth/access_token');
        $data = [
            'json' => [
                'client_id' => $this->getOptions()->getApiKey(),
                'client_secret' => $this->getOptions()->getApiSecret(),
                'code' => $code,
            ],
        ];
        try {
            $response = $this->getClient()->post($url, $data);
            return $this->responseToArray($response->getBody()->getContents());
        } catch (\Exception $e) {
            $body = json_decode($e->getResponse()->getBody()->getContents());
            throw new \Exception($body->error_description ?? $body->errors);
        }
    }

    public function getShopInfo()
    {
        if ($this->getOptions()->getApiSecret() === null || $this->getOptions()->getApiKey() === null) {
            // Key and secret required
            throw new \Exception('API key or secret is missing');
        }
        // Do a JSON POST request to grab the access token
        $url = $this->getBaseUri()->withPath("/admin/api/{$this->getOptions()->getApiVersion()}/shop.json");
        try {
            $response = $this->getClient()->get($url);
            return $this->responseToArray($response->getBody()->getContents());
        } catch (\Exception $e) {
            $body = json_decode($e->getResponse()->getBody()->getContents());
            throw new \Exception($body->errors);
        }
    }


    /**
     * Execute request
     *
     * @param string $method
     * @param string $path
     * @param array $options
     * @return array
     * @throws ShopifyApiCallException
     */
    public function request(string $method, string $path, array $options = [])
    {
        try {
            $finalPath = str_replace("{{api_version}}", $this->getOptions()->getApiVersion(), $path);
            $url = $this->getBaseUri()->withPath($finalPath);
            $response = $this->getClient()->request($method, $url, $options);
            return $this->responseToArray($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new ShopifyApiCallException($e->getMessage());
        } catch (\Exception $e) {
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $message = $body['errors'];
            if (is_array($body['errors'])) {
                $message = '';
                foreach ($body['errors'] as $key => $msg) {
                    $message .= "[$key: $msg]";
                }
            }
            throw new ShopifyApiCallException($message);
        }
    }

    /**
     * Decode response body to array
     *
     * @param mixed $body
     * @return array
     */
    private function responseToArray($body): array
    {
        return json_decode($body, true, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * Set base uri for client
     *
     * @param string|null $uri
     * @return $this
     */
    public function setShopDomain(?string $uri): Rest
    {
        $this->shopDomain = $uri;
        return $this;
    }

    /**
     * Get client base uri based on shop domain
     *
     * @return Uri
     * @throws \Exception
     */
    public function getBaseUri(): Uri
    {
        if ($this->shopDomain === null) {
            // Shop is required
            throw new \Exception('Shopify domain missing for API calls');
        }

        if (is_null($this->baseUri)) {
            $this->baseUri = new Uri("https://{$this->shopDomain}");
        }
        return $this->baseUri;
    }

    /**
     * Set client base uri
     *
     * @param Uri $uri
     * @return $this
     */
    public function setBaseUri(Uri $uri): Rest
    {
        $this->baseUri = $uri;
        return $this;
    }

    /**
     * @return ClientOptions
     */
    public function getOptions(): ClientOptions
    {
        return $this->options;
    }

    /**
     * Set options for client
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): Rest
    {
        $this->getOptions()->setData(array_merge($this->getOptions()->getData(), $options));
        return $this;
    }

    /**
     * Get the client
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}
