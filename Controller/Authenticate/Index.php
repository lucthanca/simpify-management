<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Controller\Authenticate;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

class Index implements ActionInterface
{
    private RequestInterface $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    public function execute()
    {
        dd($this->getRequest());
        dd('auth');
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
