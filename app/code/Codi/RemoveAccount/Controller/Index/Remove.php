<?php
namespace Codi\RemoveAccount\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Remove extends Action
{
    protected $_sessionFactory;
    protected $_messageManager;
    protected $_customerFactory;
    protected $_registry;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_scopeConfig;

    public function __construct(
        Context $context,
        SessionFactory $sessionFactory,
        ManagerInterface $messageManager,
        CustomerFactory $customerFactory,
        Registry $registry,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_sessionFactory = $sessionFactory;
        $this->_messageManager = $messageManager;
        $this->_customerFactory = $customerFactory;
        $this->_registry = $registry;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $customerSession = $this->_sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getCustomerId();
            $customer = $this->_customerFactory->create()->load($customerId);

            // Store customer email before deletion
            $customerEmail = $customer->getEmail();

            // Attempt to logout the customer first
            try {
                $customerSession->logout();
            } catch (\Exception $e) {
                $this->_logger->critical("Error during customer logout: " . $e->getMessage());
                // Proceed with deletion even if logout fails
            }

            // Delete customer
            $this->deleteCustomer($customer);

            // Send email notification
            $this->sendDeletionEmail($customerEmail);

            $this->getResponse()->setRedirect($this->_storeManager->getStore()->getBaseUrl());
            $this->_messageManager->addSuccess(__('Your account has been successfully deleted.'));
        } else {
            $this->_messageManager->addError(__('Please log in to delete your account.'));
            $this->_redirect('customer/account/login');
        }
    }

    private function deleteCustomer($customer)
    {
        $this->_registry->register('isSecureArea', true);
        
        try {
            // Add more deletion logic here if needed
            $customer->delete();
        } catch (\Exception $e) {
            $this->_messageManager->addError(__('An error occurred while deleting your account. Please try again later.'));
            $this->_logger->critical($e);
        }

        $this->_registry->unregister('isSecureArea');
    }

    private function sendDeletionEmail($customerEmail)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $templateId = 'customer_account_deleted_template';

        $templateVars = [
            'store' => $this->_storeManager->getStore(),
        ];

        $from = ['email' => $this->_scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), 'name' => $this->_scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)];

        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $storeId
        ];

        $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($customerEmail)
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
}