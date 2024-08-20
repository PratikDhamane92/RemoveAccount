<?php
namespace Codi\StuartShipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StuartJob extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('stuart_job', 'entity_id');
    }
}