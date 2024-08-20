<?php
/**
 * Codi_RemoveAccount
 */

namespace Codi\RemoveAccount\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\SessionFactory;

class Delete extends Action
{
    protected $_pageFactory;


    public function __construct(
        Context $context,
        PageFactory $pageFactory, 
        SessionFactory $sessionFactory
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_sessionFactory = $sessionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $customer = $this->_sessionFactory->create();

        if ($customer->isLoggedIn()) {
            $page = $this->_pageFactory->create();
            $page->getConfig()->getTitle()->set('Delete Customer');
            return $page;
        }
        else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        // TODO: Implement execute() method.
    }
}