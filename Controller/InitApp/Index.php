<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Controller\InitApp;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use SimiCart\SimpifyManagement\Api\Data\ShopInterface as IShop;
use SimiCart\SimpifyManagement\Api\ShopRepositoryInterface as IShopRepository;
use SimiCart\SimpifyManagement\Helper\PageLayoutTrait;
use SimiCart\SimpifyManagement\Model\VerifyShopify;
use SimiCart\SimpifyManagement\Registry\CurrentShop as RCurrentShop;
use \Psr\Log\LoggerInterface as ILogger;
use Magento\Framework\View\Element\Template;

class Index implements HttpGetActionInterface
{
    use PageLayoutTrait;

    private RequestInterface $request;
    private UrlInterface $url;
    private PageFactory $pageFactory;
    private RCurrentShop $currentShopRegistry;
    private IShopRepository $shopRepository;
    private ILogger $logger;
    private VerifyShopify $verifyShopify;
    private RedirectFactory $redirectFactory;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param PageFactory $pageFactory
     * @param RCurrentShop $currentShopReg
     * @param IShopRepository $shopRepository
     * @param ILogger $logger
     * @param VerifyShopify $verifyShopify
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $url,
        PageFactory $pageFactory,
        RCurrentShop $currentShopReg,
        IShopRepository $shopRepository,
        ILogger $logger,
        VerifyShopify $verifyShopify,
        RedirectFactory $redirectFactory
    ) {
        $this->request = $request;
        $this->url = $url;
        $this->pageFactory = $pageFactory;
        $this->currentShopRegistry = $currentShopReg;
        $this->shopRepository = $shopRepository;
        $this->logger = $logger;
        $this->verifyShopify = $verifyShopify;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Init shop and return to full page redirect or dashboard
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): ResultInterface
    {
        try {
            [$statusCode, $data] = $this->verifyShopify->execute($this->getRequest());
        } catch (\Exception $e) {
            dd($e);
            $this->logger->critical('INIT SHOP FAILED: ' . $e);
            return $this->getPageFactory()->create(false, [
                'template' => 'SimiCart_SimpifyManagement::initApp/404.phtml',
            ]);
        }

        switch ($statusCode) {
            case 'logged_in':
                return $this->redirectFactory->create()->setPath('simpify/dashboard', $data);
                $page = $this->getPageFactory()->create(false, [
                    'template' => 'SimiCart_SimpifyManagement::authenticate/token_root.phtml',
                ]);

                $page->getLayout()->getUpdate()->addHandle('handler_simpify_dashboard');
                $page->getLayout()->unsetElement('require.js');
                $page->getLayout()->unsetElement('after.body.start');
                $this->removeMagentoBlocks($page, 'page.wrapper', ['main.content', 'before.body.end']);
                $this->removeMagentoBlocks($page, 'main.content', ['columns']);
                $this->removeMagentoBlocks($page, 'columns', ['main']);
                $this->removeMagentoBlocks($page, 'main', ['token_shimmer', 'simpify_page_wrapper']);
                $this->removeMagentoBlocks($page, 'before.body.end', []);
                return $page;
            case 'token_missing':
//                vadu_html(['init_app_sessid' => $this->getRequest()->getParam('session')]);
                return $this->redirectFactory->create()->setPath('simpify/authenticate/token', $data);
            default :
                // status === new_shop
                $page = $this->getPageFactory()->create(false, [
                    'template' => 'SimiCart_SimpifyManagement::initApp/fullpageRedirect.phtml',
                ]);
                $page->getLayout()->getUpdate()->addHandle('handler_simpify_fullpage_redirect');
                $unnecessaryHeadBlocks = $page->getLayout()->getChildBlocks('head.additional');
                $unnecessaryHeadBlocksFiltered = array_filter($unnecessaryHeadBlocks, function ($block) {
                    return $block->getNameInLayout() !== 'head_fullpage_redirect_script' ;
                });
                /* @var Template $block */
                foreach ($unnecessaryHeadBlocksFiltered as $block) {
                    $page->getLayout()->unsetChild('head.additional', $block->getNameInLayout());
                }
                // Remove require js block
                $page->getLayout()->unsetElement('require.js');

                return $page;
        }
    }

    /**
     * Get request
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Get url builder
     *
     * @return UrlInterface
     */
    public function getUrlBuilder(): UrlInterface
    {
        return $this->url;
    }

    /**
     * Get page result factory
     *
     * @return PageFactory
     */
    public function getPageFactory(): PageFactory
    {
        return $this->pageFactory;
    }
}
