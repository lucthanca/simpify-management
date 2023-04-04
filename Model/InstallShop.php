<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface;
use SimiCart\SimpifyManagement\Api\ShopRepositoryInterface as IShopRepository;

class InstallShop
{
    protected IShopRepository $shopRepository;

    /**
     * @param IShopRepository $shopRepository
     */
    public function __construct(
        IShopRepository $shopRepository
    ) {
        $this->shopRepository = $shopRepository;
    }

    public function execute(string $shopDomain, ?string $code)
    {
        try {
            $shop = $this->shopRepository->getByDomain($shopDomain);
            if (!$shop->getId()) {
                $this->createShop($shopDomain);
                $shop = $this->shopRepository->getByDomain($shopDomain);
            }
        } catch (\Exception $e) {}
    }

    /**
     * Create not shop using shop domain
     *
     * @param string $shopDomain
     * @param string|null $token
     * @throws CouldNotSaveException
     */
    protected function createShop(string $shopDomain, ?string $token = null)
    {
        $shopData = [
            ShopInterface::SHOP_NAME => $shopDomain,
            ShopInterface::SHOP_DOMAIN => $shopDomain,
            ShopInterface::SHOP_EMAIL => "shop@$shopDomain",
            ShopInterface::SHOP_STOREFRONT_TOKEN => $token ?? "",
        ];

        $this->shopRepository->createShop($shopData);
    }
}
