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
        // Register webhook with stuart account
        $result = $this->resultJsonFactory->create();

        try {
            $baseUrl = $this->urlBuilder->getBaseUrl();
            $webhookUrl = $baseUrl . 'stuart/webhook/update';
            $response = $this->stuartApiClient->registerWebhook($webhookUrl);

            if (isset($response['id'])) {
                $message = __('Webhook registered successfully. ID: %1', $response['id']);
                return $result->setData(['success' => true, 'message' => $message]);
            }elseif (isset($response['data']['url'])){
                $message = __('Webhook url has already been taken/registered.');
                return $result->setData(['success' => true, 'message' => $message]);
            } else {
                throw new \Exception(__('Failed to register webhook'));
            }
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}