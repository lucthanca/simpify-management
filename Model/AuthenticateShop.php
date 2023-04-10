<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\App\RequestInterface as IRequest;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;
use SimiCart\SimpifyManagement\Exceptions\SignatureVerificationException;

/**
 * Verify and authenticate shop
 */
class AuthenticateShop
{
    private InstallShop $installShop;
    private IRequest $request;

    /**
     * Authenticate Shop Constructor
     *
     * @param InstallShop $installShop
     * @param IRequest $request
     */
    public function __construct(
        InstallShop $installShop,
        IRequest $request
    ) {
        $this->installShop = $installShop;
        $this->request = $request;
    }

    /**
     * Execute authenticate and update shop info to local system
     *
     * @param IShop $shop
     * @param string|null $code
     * @return bool
     * @throws SignatureVerificationException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(IShop $shop, ?string $code = null)
    {
        // If no code => on install app. do nothing, let block build auth url
        if (empty($code)) {
            return false;
        }
        if (!$shop->getShopApi()->verifyRequest($this->getRequest()->getParams())) {
            throw new SignatureVerificationException(__('HMAC verification failed.'));
        }
        try {
            $this->installShop->execute($shop, $code);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Get request
     *
     * @return IRequest
     */
    public function getRequest(): IRequest
    {
        return $this->request;
    }

    /**
     * Get install shop action
     *
     * @return InstallShop
     */
    public function getInstallShop(): InstallShop
    {
        return $this->installShop;
    }
}
