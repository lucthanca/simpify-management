<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface;

class Shop extends AbstractModel implements ShopInterface
{
    protected $_eventPrefix = 'simpify_shop';

    protected $_eventObject = 'shop';

    protected Json $jsonSerializer;

    /**
     * @param Json $jsonSerializer
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Json $jsonSerializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\SimiCart\SimpifyManagement\Model\ResourceModel\Shop::class);
    }

    public function install(string $shop, ?string $code)
    {

    }

    public function getShopDomain(): string
    {
        return $this->getData(self::SHOP_DOMAIN);
    }

    public function setShopDomain(string $domain): ShopInterface
    {
        return $this->setData(self::SHOP_DOMAIN, $domain);
    }

    public function getShopName(): ?string
    {
        return $this->getData(self::SHOP_NAME);
    }

    public function setShopName(?string $name): ShopInterface
    {
        return $this->setData(self::SHOP_NAME, $name);
    }

    public function getShopEmail(): ?string
    {
        return $this->getData(self::SHOP_EMAIL);
    }

    public function setShopEmail(?string $email): ShopInterface
    {
        return $this->setData(self::SHOP_EMAIL, $email);
    }

    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    public function setStatus(int $status = 0): ShopInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getAppInfo(): string
    {
        return $this->getData(self::APP_INFO);
    }

    public function setAppInfo($info): ShopInterface
    {
        if (is_array($info)) {
            $info = $this->convertToJson($info);
        }

        return $this->setData(self::APP_INFO, $info);
    }

    public function getPlanId(): int
    {
        return (int) $this->getData(self::PLAN_ID);
    }

    public function setPlanId(int $id): ShopInterface
    {
        return $this->setData(self::PLAN_ID, $id);
    }

    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $time): ShopInterface
    {
        return $this->setData(self::CREATED_AT, $time);
    }

    public function getShopStoreFrontApiToken(): ?string
    {
        return $this->getData(self::SHOP_STOREFRONT_TOKEN);
    }

    public function setShopStoreFrontApiToken(?string $api): ShopInterface
    {
        return $this->setData(self::SHOP_STOREFRONT_TOKEN, $api);
    }

    public function getSimiAccessToken(): ?string
    {
        return $this->getData(self::SIMI_ACCESS_TOKEN);
    }

    public function setSimiAccessToken(?string $api): ShopInterface
    {
        return $this->setData(self::SIMI_ACCESS_TOKEN, $api);
    }
}
