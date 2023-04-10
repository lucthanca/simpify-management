<?php

namespace SimiCart\SimpifyManagement\Controller\Authenticate;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface as IRequest;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Result\PageFactory;
use SimiCart\SimpifyManagement\Helper\PageLayoutTrait;

class Token implements HttpGetActionInterface
{
    use PageLayoutTrait;

    private PageFactory $pageFactory;
    private IRequest $request;

    /**
     * @param PageFactory $pageFactory
     * @param IRequest $request
     */
    public function __construct(
        PageFactory $pageFactory,
        IRequest $request
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
    }

    public function execute()
    {
        $page = $this->getPageFactory()->create(false, [
            'template' => 'SimiCart_SimpifyManagement::authenticate/token_root.phtml',
        ]);

        $unnecessaryHeadBlocks = $page->getLayout()->getChildBlocks('head.additional');
        /* @var Template $block */
        foreach ($unnecessaryHeadBlocks as $block) {
            if ($block->getNameInLayout() !== 'head_token_style') {
                $page->getLayout()->unsetChild('head.additional', $block->getNameInLayout());
            }
        }
        $page->getLayout()->unsetElement('require.js');
        $page->getLayout()->unsetElement('after.body.start');
        $this->removeMagentoBlocks($page, 'page.wrapper', ['main.content', 'before.body.end']);
        $this->removeMagentoBlocks($page, 'main.content', ['columns']);
        $this->removeMagentoBlocks($page, 'columns', ['main']);
        $this->removeMagentoBlocks($page, 'main', ['token_shimmer']);
        $this->removeMagentoBlocks($page, 'before.body.end', ['token_script_base']);
        return $page;
    }

    /**
     * @return PageFactory
     */
    public function getPageFactory(): PageFactory
    {
        return $this->pageFactory;
    }
}
