<?php
namespace Codi\StuartShipping\Controller\Adminhtml\Webhook;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Codi\StuartShipping\Model\Api\StuartApiClient;
use Magento\Framework\UrlInterface;

class Register extends Action
{
    protected $resultJsonFactory;
    protected $stuartApiClient;
    protected $urlBuilder;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StuartApiClient $stuartApiClient,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->stuartApiClient = $stuartApiClient;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $webhookUrl = $this->urlBuilder->getUrl('stuart/webhook/update', ['_secure' => true]);
            $response = $this->stuartApiClient->registerWebhook($webhookUrl);

            if (isset($response['id'])) {
                $message = __('Webhook registered successfully. ID: %1', $response['id']);
                return $result->setData(['success' => true, 'message' => $message]);
            } else {
                throw new \Exception(__('Failed to register webhook'));
            }
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}