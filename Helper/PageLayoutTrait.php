<?php

namespace SimiCart\SimpifyManagement\Helper;

use Magento\Framework\Controller\ResultInterface as IResult;

trait PageLayoutTrait
{
    /**
     * Remove magento blocks
     *
     * @param IResult $page
     * @param string $parent
     * @param array $keepChildren
     * @return void
     */
    protected function removeMagentoBlocks(IResult $page, string $parent, array $keepChildren = [])
    {
        foreach ($page->getLayout()->getChildNames($parent) as $na) {
            if (in_array($na, $keepChildren)) {
                continue;
            }
            $page->getLayout()->unsetElement($na);
        }
    }
}
