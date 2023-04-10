<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use SimiCart\SimpifyManagement\Model\Session;

class Dashboard extends \Magento\Framework\View\Element\Template
{
    private Session $shopSession;
    private \Magento\Customer\Model\Session $customerSession;

    /**
     * @param Context $context
     * @param Session $shopSession
     * @param array $data
     */
    public function __construct(Template\Context $context, Session $shopSession, \Magento\Customer\Model\Session $customerSession,array $data = [])
    {
        parent::__construct($context, $data);
        $this->shopSession = $shopSession;
        $this->customerSession = $customerSession;
    }

    public function getShop()
    {
        dd($this->getRequest()->getParams());

        $this->shopSession->getShop();
    }
}
