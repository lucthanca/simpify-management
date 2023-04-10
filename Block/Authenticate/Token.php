<?php
declare(strict_types=1);

namespace SimiCart\SimpifyManagement\Block\Authenticate;

class Token extends \SimiCart\SimpifyManagement\Block\InitApp\FullPageRedirect
{

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget(): ?string
    {
        return $this->getRequest()->getParam('target');
    }

    /**
     * Get request notice
     *
     * @return string|null
     */
    public function getNotice(): ?string
    {
        return $this->getRequest()->getParam('notice');
    }

    /**
     * Get error request
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->getRequest()->getParam('error');
    }

    /**
     * @inheritDoc
     */
    protected function getCacheLifetime()
    {
        // No Cache for fix redirect error
        return null;
    }
}
