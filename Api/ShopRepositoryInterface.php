<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface;

interface ShopRepositoryInterface
{
    /**
     * Retrieve shop by shop domain
     *
     * @param string $domain
     * @return ShopInterface
     * @throws NoSuchEntityException
     */
    public function getByDomain(string $domain): ShopInterface;

    /**
     * Store shop
     *
     * @param ShopInterface $shop
     * @return ShopInterface
     * @throws CouldNotSaveException
     */
    public function save(ShopInterface $shop): ShopInterface;
}
