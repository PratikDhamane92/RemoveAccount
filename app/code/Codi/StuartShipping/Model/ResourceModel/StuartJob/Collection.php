<?php
namespace Codi\StuartShipping\Model\ResourceModel\StuartJob;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Codi\StuartShipping\Model\StuartJob::class,
            \Codi\StuartShipping\Model\ResourceModel\StuartJob::class
        );
    }
}