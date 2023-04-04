<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Block\InitApp;

use Magento\Framework\View\Element\Template;

class FullPageRedirect extends Template
{
    public function getApiKey(): string
    {
        return 'c5f442698851adf4a90fd5fe4d344ba2';
    }
    public function getHost(): string
    {
        return $this->getRequest()->getParam('host');
    }

    public function getShop(): string
    {
        return $this->getRequest()->getParam('shop');
    }

    public function getRedirectUrl(): string
    {
        return $this->getUrl('simicart/authenticate');
    }

    public function getAppBridgeVersion(): string
    {
        return 'latest';
    }
}
