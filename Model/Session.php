<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface as IManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\Session\ValidatorInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;
use SimiCart\SimpifyManagement\Api\ShopRepositoryInterface as IShopRepository;

class Session extends \Magento\Framework\Session\SessionManager
{
    protected ?IShop $shopModel = null;

    protected ?int $isShopIdChecked;
    protected IShopRepository $shopRepository;
    protected IManager $eventManager;

    /**
     * @param IManager $eventManager
     * @param IShopRepository $shopRepository
     * @param Http $request
     * @param SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param SaveHandlerInterface $saveHandler
     * @param ValidatorInterface $validator
     * @param StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param State $appState
     * @param SessionStartChecker|null $sessionStartChecker
     * @throws SessionException
     */
    public function __construct(
        IManager                                               $eventManager,
        IShopRepository                                        $shopRepository,
        \Magento\Framework\App\Request\Http                    $request,
        SidResolverInterface                                   $sidResolver,
        ConfigInterface                                        $sessionConfig,
        SaveHandlerInterface                                   $saveHandler,
        ValidatorInterface                                     $validator,
        StorageInterface                                       $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface       $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State                           $appState,
        SessionStartChecker                                    $sessionStartChecker = null
    ) {
        parent::__construct($request, $sidResolver, $sessionConfig, $saveHandler, $validator, $storage, $cookieManager, $cookieMetadataFactory, $appState, $sessionStartChecker);
        $this->shopRepository = $shopRepository;
        $this->eventManager = $eventManager;
    }

    /**
     * Set shop as login in
     *
     * @param IShop $shop
     * @return $this
     */
    public function setShopAsLoginIn(IShop $shop)
    {
        $this->regenerateId();
        $this->setShop($shop);
        $this->eventManager->dispatch('simpify_Shop_login', ['shop' => $shop]);
        return $this;
    }

    /**
     * Login shop by id
     *
     * @param int $shopId
     * @return bool
     */
    public function loginById(int $shopId): bool
    {
        try {
            $shop = $this->shopRepository->getById($shopId);
            $this->setShopAsLoginIn($shop);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set shopify shop model and session shop id
     *
     * @param IShop $shop
     * @return $this
     */
    public function setShop(IShop $shop): self
    {
        $this->shopModel = $shop;
        $this->setShopId((int) $shop->getId());

        return $this;
    }

    /**
     * Get session shop
     *
     * @return IShop|null
     */
    public function getShop(): ?IShop
    {
        if ($this->shopModel === null) {
            dd($this->getShopId());
            if ($this->getShopId()) {
                $this->shopModel = $this->shopRepository->getById((int) $this->getShopId());
            }
        }

        return $this->shopModel;
    }

    /**
     * Set shopify shop ID
     *
     * @param int $shopId
     * @return $this
     */
    public function setShopId(int $shopId): self
    {
        $this->storage->setData('simpify_shop_id', $shopId);
        return $this;
    }

    /**
     * Retrieve shop id from current session
     *
     * @api
     * @return int|null
     */
    public function getShopId(): ?int
    {
        if ($this->storage->getData('simpify_shop_id')) {
            return $this->storage->getData('simpify_shop_id');
        }
        return null;
    }

    /**
     * Retrieve shop id from current session
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getShopId();
    }

    /**
     * Set shop id
     *
     * @param mixed $shopId
     * @return $this
     */
    public function setId($shopId): self
    {
        return $this->setShopId((int) $shopId);
    }

    /**
     * Reset core session hosts after resetting session ID
     *
     * @return $this
     */
    public function regenerateId()
    {
        parent::regenerateId();
        $this->_cleanHosts();
        return $this;
    }

    /**
     * Checking customer login status
     *
     * @api
     * @return bool
     */
    public function isShopLoggedIn()
    {
        return (bool) $this->getShopId()
            && $this->checkShopId((int) $this->getId());
    }

    /**
     * Light checkt shop exists
     *
     * @param int $shopId
     * @return bool
     */
    public function checkShopId(int $shopId): bool
    {
        if ($this->isShopIdChecked === $shopId) {
            return true;
        }
        try {
            $shop = $this->shopRepository->getById($shopId);
            if (!$shop->getId() || !$shop->hasOfflineAccess() || $shop->hasUninstalled()) {
                throw new LocalizedException(__("Shop not active"));
            }
            $this->isShopIdChecked = $shopId;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
