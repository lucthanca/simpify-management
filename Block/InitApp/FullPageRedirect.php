<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Block\InitApp;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use SimiCart\SimpifyManagement\Model\ConfigProvider;
use SimiCart\SimpifyManagement\Model\InstallShop;
use SimiCart\SimpifyManagement\Model\Source\AuthMode;
use SimiCart\SimpifyManagement\Registry\CurrentShop;

class FullPageRedirect extends Template
{
    protected ConfigProvider $configProvider;
    protected InstallShop $installShop;
    protected CurrentShop $currentShop;

    /**
     * @param ConfigProvider $configProvider
     * @param Context $context
     * @param InstallShop $installShop
     * @param CurrentShop $currentShop
     * @param array $data
     */
    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        InstallShop $installShop,
        CurrentShop $currentShop,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->installShop = $installShop;

        // Cache the block by the shop domain and host
        $this->setData('cache_key', "{$this->getRequest()->getParam('shop')}_{$this->getRequest()->getParam('host')}");
        $this->currentShop = $currentShop;
    }

    /**
     * Get store config Shopify API key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->configProvider->getApiKey();
    }

    /**
     * Get request host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->getRequest()->getParam('host');
    }

    /**
     * Get request shop domain
     *
     * @return string
     */
    public function getShop(): string
    {
        vadu_html($this->getRequest()->getParams());
        return $this->getRequest()->getParam('shop');
    }

    /**
     * Get code param
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->getRequest()->getParam('code');
    }

    /**
     * Authenticate shop and return auth url
     *
     * @return string
     */
    public function getAuthUrl(): string
    {
        $grantMode = $this->currentShop->get()->hasOfflineAccess() ?
            $this->configProvider->getApiGrantMode() :
            AuthMode::OFFLINE;
        return $this->currentShop->get()->getShopApi()->buildAuthUrl($grantMode, $this->configProvider->getApiScopes());
    }

    /**
     * Get config Shopify App Bridge Version
     *
     * @return string
     */
    public function getAppBridgeVersion(): string
    {
        return "@{$this->configProvider->getAppBridgeVersion()}";
    }

    /**
     * @inheritDoc
     */
    protected function getCacheLifetime()
    {
        return parent::getCacheLifetime() ?: 7200;
    }
}
