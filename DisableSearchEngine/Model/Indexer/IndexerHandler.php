<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\DisableSearchEngine\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;

class IndexerHandler implements IndexerInterface
{
    /**
     * @inheritDoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function cleanIndex($dimensions)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }
}
