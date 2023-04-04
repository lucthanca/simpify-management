<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Controller\InitApp;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    private RequestInterface $request;
    private UrlInterface $url;
    private PageFactory $pageFactory;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param PageFactory $pageFactory
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $url,
        PageFactory $pageFactory
    ) {
        $this->request = $request;
        $this->url = $url;
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $page = $this->getPageFactory()->create(false, [
            'template' => 'SimiCart_SimpifyManagement::initApp/fullpageRedirect.phtml',
        ]);
        $unnecessaryHeadBlocks = $page->getLayout()->getChildBlocks('head.additional');
        $unnecessaryHeadBlocksFiltered = array_filter($unnecessaryHeadBlocks, function ($block) {
            return $block->getNameInLayout() !== 'head_fullpage_redirect_script' ;
        });
        /** @var \Magento\Framework\View\Element\Template $block */
        foreach ($unnecessaryHeadBlocksFiltered as $block) {
            $page->getLayout()->unsetChild('head.additional', $block->getNameInLayout());
        }
        // Remove require js block
        $page->getLayout()->unsetElement('require.js');
        return $page;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getUrlBuilder(): UrlInterface
    {
        return $this->url;
    }

    /**
     * @return PageFactory
     */
    public function getPageFactory(): PageFactory
    {
        return $this->pageFactory;
    }
}
