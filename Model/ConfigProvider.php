<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider
{
    const API_KEY_CONFIG_XML_PATH = 'simpify_management/general/api_key';
    const API_SECRET_CONFIG_XML_PATH = 'simpify_management/general/api_secret';
    const APP_BRIDGE_VERSION_CONFIG_XML_PATH = 'simpify_management/general/app_bridge_version';

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
        if (!$value) {
            return '3';
        }
        return $value;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }
}
