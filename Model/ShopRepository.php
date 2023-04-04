<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface;
use SimiCart\SimpifyManagement\Api\ShopRepositoryInterface;
use SimiCart\SimpifyManagement\Model\ResourceModel\Shop as ShopResource;

class ShopRepository implements ShopRepositoryInterface
{
    protected ShopFactory $shopFactory;
    protected ShopResource $shopResource;
    protected \Psr\Log\LoggerInterface $logger;

    /**
     * @param ShopFactory $shopFactory
     * @param ShopResource $shopResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShopFactory $shopFactory,
        ShopResource $shopResource,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->shopFactory = $shopFactory;
        $this->shopResource = $shopResource;
        $this->logger = $logger;
    }

    /**
     * Get new instance of Shop
     *
     * @return ShopInterface
     */
    public function newInstance(): ShopInterface
    {
        return $this->shopFactory->create();
    }


    /**
     * Create a shop using provided data
     *
     * @param array $data
     * @return ShopInterface
     * @throws CouldNotSaveException
     */
    public function createShop(array $data): ShopInterface
    {
        $shop = $this->newInstance();
        $shop->setData($data);
        return $this->save($shop);
    }

    public function getByDomain(string $domain): ShopInterface
    {
        try {
            $shop = $this->newInstance();
            $this->shopResource->load($shop, $domain, ShopInterface::SHOP_DOMAIN);
            return $shop;
        } catch (\Exception $e) {
            $this->logger->debug($e);
            throw new NoSuchEntityException(__("Something went wrong while loading Shop. Please review the log."));
        }
    }

    public function save(ShopInterface $shop): ShopInterface
    {
        try {
            $this->shopResource->save($shop);
            return $shop;
        } catch (\Exception $e) {
            $this->logger->debug($e);
            throw new CouldNotSaveException(__("Failed to save Shop. Please review the log."));
        }
    }
}
