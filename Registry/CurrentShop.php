<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Registry;

use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;
use SimiCart\SimpifyManagement\Model\ShopFactory as FShop;

class CurrentShop
{

    private IShop $shop;

    private FShop $shopFactory;

    /**
     * @param FShop $shopFactory
     */
    public function __construct(FShop $shopFactory)
    {
        $this->shopFactory = $shopFactory;
    }

    /**
     * Set current shop
     *
     * @param IShop $shop
     * @return self
     */
    public function set(IShop $shop): self
    {
        $this->shop = $shop;
        return $this;
    }

    /**
     * Return current shop or new instance of shop
     *
     * @return IShop
     */
    public function get(): IShop
    {
        return $this->shop ?? $this->shopFactory->create();
    }
}
