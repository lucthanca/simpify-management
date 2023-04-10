<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;
use SimiCart\SimpifyManagement\Api\ShopRepositoryInterface as IShopRepository;

/**
 * Authenticate and install shop
 */
class InstallShop
{
    protected IShopRepository $shopRepository;
    protected ConfigProvider $configProvider;

    /**
     * @param IShopRepository $shopRepository
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        IShopRepository $shopRepository,
        ConfigProvider $configProvider
    ) {
        $this->shopRepository = $shopRepository;
        $this->configProvider = $configProvider;
    }

    /**
     * Execution.
     *
     * @param IShop $shop
     * @param string|null $code
     * @throws CouldNotSaveException
     */
    public function execute(IShop $shop, ?string $code = null)
    {
        // if the store has been deleted, restore the store to set the access token
        if ($shop->hasUninstalled()) {
            $shop->restore();
        }
        if (!$shop->hasOfflineAccess()) {
            // Get the data and set the access token
            $data = $shop->getShopApi()->getAccessData($code);
            $shop->setAccessToken($data['access_token']);
        }
        if (!$shop->hasStorefrontToken()) {
            $token = $shop->getShopApi()->requestStorefrontToken();
            $shop->setShopStorefrontToken($token);
        }
        $shopInfo = $shop->getShopApi()->getShopInfo();
        if (!empty($shopInfo)) {
            $shop->setShopName($shopInfo['name'] ?? $shop->getShopName());
            $shop->setShopEmail($shopInfo['email'] ?? $shop->getShopEmail());
        }

        $this->shopRepository->save($shop);
    }
}
