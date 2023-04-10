<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Model\Clients\Stacks;

use Exception;
use SimiCart\SimpifyManagement\Model\Clients\ClientOptions;
use SimiCart\SimpifyManagement\Model\Clients\ShopifyClientInterface as IShopifyClient;
use Psr\Http\Message\RequestInterface;

class AuthRequest
{
    protected IShopifyClient $api;

    public function __construct(?IShopifyClient $api = null)
    {
        $this->api = $api;
    }

    /**
     * Run.
     *
     * @param callable $handler
     *
     * @throws Exception For missing API key or password for private apps.
     * @throws Exception For missing access token on GraphQL calls.
     *
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        $self = $this;

        return function (RequestInterface $request, array $options) use ($self, $handler) {
            // Get the request URI
            $uri = $request->getUri();
            $isPrivate = $self->api->getOptions()->isPrivate();
            $apiKey = $self->api->getOptions()->getApiKey();
            $apiPassword = null; // TODO: use $self->api->getOptions()->getApiPassword() for private api call support;
            $accessToken = $self->api->getOptions()->getShop()->getAccessToken();

            if ($self->isAuthableRequest((string) $uri)) {
                if ($self->isRestRequest((string) $uri)) {
                    // Checks for REST
                    if ($isPrivate && ($apiKey === null || $apiPassword === null)) {
                        // Key and password are required for private API calls
                        throw new Exception('API key and password required for private Shopify REST calls');
                    }

                    if ($isPrivate) {
                        // Private: Add auth for REST calls, add the basic auth header
                        $request = $request->withHeader(
                            'Authorization',
                            'Basic '.base64_encode("{$apiKey}:{$apiPassword}")
                        );
                    } else {
                        // Public: Add the token header
                        $request = $request->withHeader(IShopifyClient::HEADER_ACCESS_TOKEN, $accessToken);
                    }
                } else {
                    // Checks for Graph
                    if ($isPrivate && ($apiPassword === null && $accessToken === null)) {
                        // Private apps need password for use as access token
                        throw new Exception('API password/access token required for private Shopify GraphQL calls');
                    } elseif (!$isPrivate && $accessToken === null) {
                        // Need access token for public calls
                        throw new Exception('Access token required for public Shopify GraphQL calls');
                    }

                    // Public/Private: Add the token header
                    $request = $request->withHeader(
                        IShopifyClient::HEADER_ACCESS_TOKEN,
                        $apiPassword ?? $accessToken
                    );
                }
            }

            // Adjust URI path to be versioned
            $uri = $request->getUri();
            $request = $request->withUri(
                $uri->withPath(
                    $this->versionPath($uri->getPath())
                )
            );

            return $handler($request, $options);
        };
    }

    /**
     * Versions the API call with the set version.
     *
     * @param string $uri The request URI.
     *
     * @return string
     */
    protected function versionPath(string $uri): string
    {
        $version = $this->api->getOptions()->getVersion();
        if ($version === null ||
            preg_match(ClientOptions::VERSION_PATTERN, $uri) ||
            !$this->isAuthableRequest($uri) ||
            !$this->isVersionableRequest($uri)
        ) {
            // No version set, or already versioned... nothing to do
            return $uri;
        }

        // Graph request
        if ($this->isGraphRequest($uri)) {
            return str_replace('/admin/api', "/admin/api/{$version}", $uri);
        }

        // REST request
        return preg_replace('/\/admin(\/api)?\//', "/admin/api/{$version}/", $uri);
    }

    /**
     * Determines if the request requires auth headers.
     *
     * @param string $uri The request URI.
     *
     * @return bool
     */
    protected function isAuthableRequest(string $uri): bool
    {
        return preg_match('/\/admin\/oauth\/(authorize|access_token)/', $uri) === 0;
    }

    /**
     * Determines if the request is to Graph API.
     *
     * @param string $uri The request URI.
     *
     * @return bool
     */
    protected function isGraphRequest(string $uri): bool
    {
        return strpos($uri, 'graphql.json') !== false;
    }

    /**
     * Determines if the request is to REST API.
     *
     * @param string $uri The request URI.
     *
     * @return bool
     */
    protected function isRestRequest(string $uri): bool
    {
        return $this->isGraphRequest($uri) === false;
    }

    /**
     * Determines if the request requires versioning.
     *
     * @param string $uri The request URI.
     *
     * @return bool
     */
    protected function isVersionableRequest(string $uri): bool
    {
        return preg_match('/\/admin\/(oauth\/access_scopes)/', $uri) === 0;
    }
}
