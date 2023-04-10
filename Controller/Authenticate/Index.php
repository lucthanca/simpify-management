<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Controller\Authenticate;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as FJson;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use SimiCart\SimpifyManagement\Api\ShopRepositoryInterface;
use SimiCart\SimpifyManagement\Model\AuthenticateShop;
use SimiCart\SimpifyManagement\Model\InstallShop;

class Index implements ActionInterface
{
    private RequestInterface $request;
    private InstallShop $installShop;
    private FJson $jsonFactory;
    private RedirectFactory $redirectFactory;
    private ShopRepositoryInterface $shopRepository;
    private AuthenticateShop $authenticateShop;

    /**
     * Authenticate shop constructor
     *
     * @param RequestInterface $request
     * @param InstallShop $installShop
     * @param FJson $jsonFactory
     * @param RedirectFactory $redirectFactory
     * @param ShopRepositoryInterface $shopRepository
     * @param AuthenticateShop $authenticateShop
     */
    public function __construct(
        RequestInterface $request,
        InstallShop $installShop,
        FJson $jsonFactory,
        RedirectFactory $redirectFactory,
        ShopRepositoryInterface $shopRepository,
        AuthenticateShop $authenticateShop
    ) {
        $this->request = $request;
        $this->installShop = $installShop;
        $this->jsonFactory = $jsonFactory;
        $this->redirectFactory = $redirectFactory;
        $this->shopRepository = $shopRepository;
        $this->authenticateShop = $authenticateShop;
    }

    public function execute()
    {
//        vadu_html([
//            "AUTHENTICATE SHOP" => $this->getRequest()->getParams()
//        ]);

        try {
            $shop = $this->shopRepository->getByDomain($this->getRequest()->getParam('shop'));
            $result = $this->authenticateShop->execute($shop, $this->getRequest()->getParam('code'));
            if ($result !== true) {
                throw new LocalizedException(__("Validation failed."));
            }
        } catch (\Exception $e) {
            return $this->jsonFactory->create()->setData(['success' => false]);
        }
        return $this->redirectFactory->create()->setPath('simpify/initapp', ['shop' => $shop->getShopDomain(), 'host' => $this->getRequest()->getParam('host')]);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
