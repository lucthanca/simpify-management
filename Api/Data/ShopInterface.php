<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Api\Data;

interface ShopInterface
{
    const SHOP_DOMAIN = 'shop_domain';
    const SHOP_NAME = 'shop_name';
    const SHOP_EMAIL = 'shop_email';
    const STATUS = 'status';
    const APP_INFO = 'app_info';
    const PLAN_ID = 'plan_id';
    const CREATED_AT = 'created_at';
    const SHOP_ACCESS_TOKEN = 'shop_access_token';
    const SHOP_STOREFRONT_TOKEN = 'shop_storefront_token';
    const SIMI_ACCESS_TOKEN = 'simi_access_token';

    const STATUS_UNINSTALLED = 0;
    const STATUS_INSTALLED = 1;

    /**
     * Retrieve Shop Domain
     *
     * @return string
     */
    public function getShopDomain(): string;

    /**
     * Set shop domain
     *
     * @param string $domain
     * @return $this
     */
    public function setShopDomain(string $domain): self;

    /**
     * Retrieve shop name
     *
     * @return string|null
     */
    public function getShopName(): ?string;

    /**
     * Set Shop name
     *
     * @param string|null $name
     * @return $this
     */
    public function setShopName(?string $name): self;

    /**
     * Retrieve shop email
     *
     * @return string|null
     */
    public function getShopEmail(): ?string;

    /**
     * Set email
     *
     * @param string|null $email
     * @return $this
     */
    public function setShopEmail(?string $email): self;

    /**
     * Get shop status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status = 0): self;

    /**
     * Retrieve app info in json
     *
     * @return string
     */
    public function getAppInfo(): string;

    /**
     * Set app info
     *
     * @param string|array $info
     * @return $this
     */
    public function setAppInfo($info): self;

    /**
     * Retrieve subscribed plan id
     *
     * @return int
     */
    public function getPlanId(): int;

    /**
     * Set plan id
     *
     * @param int $id
     * @return $this
     */
    public function setPlanId(int $id): self;

    /**
     * Retrieve first installation time in string
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set installation time
     *
     * @param string $time
     * @return $this
     */
    public function setCreatedAt(string $time): self;

    /**
     * Retrieve shop store api token
     *
     * @return string|null
     */
    public function getAccessToken(): ?string;

    /**
     * Set shop api token
     *
     * @param string|null $api
     * @return string|null
     */
    public function setAccessToken(?string $api): self;

    /**
     * Get shop storefront api token
     *
     * @return string|null
     */
    public function getShopStorefrontToken(): ?string;

    /**
     * Set shop storefront api token
     *
     * @param string|null $token
     * @return string|null
     */
    public function setShopStorefrontToken(?string $token): self;

    /**
     * Retrieve simi system token
     *
     * @return string|null
     */
    public function getSimiAccessToken(): ?string;

    /**
     * Set simi token
     *
     * @param string|null $api
     * @return string|null
     */
    public function setSimiAccessToken(?string $api): self;

    /**
     * Authorization Shop when install app.
     *
     * @param string $shop
     * @param string|null $code
     * @return mixed
     */
    public function install(string $shop, ?string $code);
}
