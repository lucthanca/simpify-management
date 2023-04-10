<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use SimiCart\SimpifyManagement\Model\Source\AuthMode;

class ConfigProvider
{
    const API_KEY_CONFIG_XML_PATH = 'simpify_management/general/api_key';
    const API_SECRET_CONFIG_XML_PATH = 'simpify_management/general/api_secret';
    const APP_BRIDGE_VERSION_CONFIG_XML_PATH = 'simpify_management/general/app_bridge_version';
    const API_GRANT_MODE_CONFIG_XML_PATH = 'simpify_management/general/shopify_api_grant_mode';
    const API_SCOPES_CONFIG_XML_PATH = 'simpify_management/general/shopify_api_scopes';
    const API_VERSION_CONFIG_XML_PATH = 'simpify_management/general/api_version';

    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve App API Key
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->scopeConfig->getValue(self::API_KEY_CONFIG_XML_PATH);
    }

    /**
     * Retrieve app api secret
     *
     * @return string|null
     */
    public function getApiSecret(): ?string
    {
        return $this->scopeConfig->getValue(self::API_SECRET_CONFIG_XML_PATH);
    }

    /**
     * Retrieve app bridge version, if no defined, return version 3 (latest version)
     *
     * @return string
     */
    public function getAppBridgeVersion(): string
    {
        $value = $this->scopeConfig->getValue(self::APP_BRIDGE_VERSION_CONFIG_XML_PATH);
        if (empty($value)) {
            return '3';
        }
        return $value;
    }

    /**
     * Get Shopify API Grant Mode
     *
     * This option is for the grant mode when authenticating.
     * Default is "OFFLINE", "PERUSER" is available as well.
     * Note: Install will always be in offline mode.
     *
     * @return int
    */
    public function getApiGrantMode(): int
    {
        $value = $this->scopeConfig->getValue(self::API_GRANT_MODE_CONFIG_XML_PATH);
        if (empty($value)) {
            return AuthMode::OFFLINE;
        }
        return (int) $value;
    }

    /**
     * Get Shopify API Scopes
     *
     * This option is for the scopes your application needs in the API.
     *
     * @return string
     */
    public function getApiScopes(): string
    {
        $scopes = $this->scopeConfig->getValue(self::API_SCOPES_CONFIG_XML_PATH);
        if (empty($scopes)) {
            return 'read_products,write_products';
        }
        return $scopes;
    }

    /**
     * Return Shopify API Version
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        $version = $this->scopeConfig->getValue(self::API_VERSION_CONFIG_XML_PATH);
        if (empty($version)) {
            return '2023-01';
        }
        return $version;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }
}
