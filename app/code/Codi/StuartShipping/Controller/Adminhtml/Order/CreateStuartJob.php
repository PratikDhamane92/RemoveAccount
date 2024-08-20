<?php
namespace Codi\StuartShipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Codi\StuartShipping\Helper\Data as StuartHelper;

class CreateStuartJob extends Action
{
    protected $stuartHelper;

    public function __construct(Context $context, StuartHelper $stuartHelper)
    {
        parent::__construct($context);
        $this->stuartHelper = $stuartHelper;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        // Implement logic to create Stuart job for the order
        // Use methods from StuartHelper
    }
}