<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Block\InitApp;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use SimiCart\SimpifyManagement\Model\ConfigProvider;

class FullPageRedirect extends Template
{
    protected ConfigProvider $configProvider;

    /**
     * @param ConfigProvider $configProvider
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function getApiKey(): string
    {
        return $this->configProvider->getApiKey();
    }
    public function getHost(): string
    {
        return $this->getRequest()->getParam('host');
    }

    public function getShop(): string
    {
        return $this->getRequest()->getParam('shop');
    }

    public function getAuthUrl(): string
    {
        return $this->getUrl('simicart/authenticate');
    }

    public function getAppBridgeVersion(): string
    {
        return $this->configProvider->getAppBridgeVersion();
    }
}
