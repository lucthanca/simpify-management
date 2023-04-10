<?php

namespace SimiCart\SimpifyManagement\Controller\Dashboard;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use SimiCart\SimpifyManagement\Helper\PageLayoutTrait;

class Index implements HttpGetActionInterface
{
    use PageLayoutTrait;
    private PageFactory $pageFactory;

    /**
     * @param PageFactory $pageFactory
     */
    public function __construct(PageFactory $pageFactory) {
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $page = $this->getPageFactory()->create(false, [
            'template' => 'SimiCart_SimpifyManagement::authenticate/token_root.phtml',
        ]);
        $page->getLayout()->getUpdate()->addHandle('handler_simpify_dashboard');
//        $page->getLayout()->unsetElement('require.js');
//        $page->getLayout()->unsetElement('after.body.start');
//        $this->removeMagentoBlocks($page, 'page.wrapper', ['main.content', 'before.body.end']);
//        $this->removeMagentoBlocks($page, 'main.content', ['columns']);
//        $this->removeMagentoBlocks($page, 'columns', ['main']);
//        $this->removeMagentoBlocks($page, 'main');
//        $this->removeMagentoBlocks($page, 'before.body.end', ['token_script_base']);

        return $page;
    }

    /**
     * Get page factory
     *
     * @return PageFactory
     */
    public function getPageFactory(): PageFactory
    {
        return $this->pageFactory;
    }
}
