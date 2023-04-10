<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;

interface ShopRepositoryInterface
{
    /**
     * Retrieve shop by shop domain
     *
     * @param mixed $shopId
     * @return IShop
     */
    public function getById($shopId): IShop;

    /**
     * Retrieve shop by shop domain
     *
     * @param string $domain
     * @return IShop
     * @throws NoSuchEntityException
     */
    public function getByDomain(string $domain): IShop;

    /**
     * Store shop
     *
     * @param IShop $shop
     * @return IShop
     * @throws CouldNotSaveException
     */
    public function save(IShop $shop): IShop;
}
