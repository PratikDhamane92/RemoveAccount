<?php
namespace Codi\StuartShipping\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Codi\StuartShipping\Helper\Data as StuartHelper;

class Update extends Action implements CsrfAwareActionInterface
{
    protected $resultJsonFactory;
    protected $orderRepository;
    protected $logger;
    protected $stuartHelper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OrderRepository $orderRepository,
        LoggerInterface $logger,
        StuartHelper $stuartHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->stuartHelper = $stuartHelper;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $payload = file_get_contents('php://input');
            //$signature = $this->getRequest()->getHeader('X-Stuart-Signature');

            $this->logger->info('Received webhook payload: ' . $payload);

            // if (!$this->validateSignature($payload, $signature)) {
            //     throw new \Exception('Invalid signature');
            // }

            $decodedPayload = json_decode($payload, true);
            
            $this->logger->info(print_r($decodedPayload,true));

            if (!$this->validateWebhook($decodedPayload)) {
                throw new \Exception('Invalid webhook payload');
            }

            $this->processWebhook($decodedPayload);

            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Error processing webhook: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function validateSignature($payload, $signature)
    {
        $secret = $this->stuartHelper->getClientSecret();
        $computedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computedSignature, $signature);
    }

    private function validateWebhook($payload)
    {
        // You might want to check for specific fields or validate a signature
        return isset($payload['topic']) && isset($payload['details']);
    }

    private function processWebhook($payload)
    {
        if (isset($payload['topic'])) {
            $topic = $payload['topic'];
            $details = $payload['details'];

            switch ($topic) {
                case 'package_created':
                    $this->processPackageCreated($details);
                    break;
                case 'courier_assigned':
                    $this->processCourierAssigned($details);
                    break;
                case 'courier_arriving':
                    $this->processCourierArriving($details);
                    break;
                case 'courier_waiting':
                    $this->processCourierWaiting($details);
                    break;
                case 'package_delivering':
                    $this->processPackageDelivering($details);
                    break;
                case 'package_delivered':
                    $this->processPackageDelivered($details);
                    break;
                case 'package_canceled':
                    $this->processPackageCanceled($details);
                    break;
                case 'courier_reassigning':
                    $this->processCourierReassigning($details);
                    break;
                default:
                    $this->logger->warning("Unhandled topic: $topic");
            }
        }else{
            $this->logger->error('Error processing webhook.');
        }
        
    }

    private function processPackageCreated($details)
    {
        $packageId = $details['package']['id'];
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];
        
        $trackingUrl = $details['package']['tracking_url'];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'Stuart package (ID: '.$packageId.') is created. Track your shipment <a href="'.$trackingUrl.'" target="_blank">here</a>.';

            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);

            $this->orderRepository->save($order);
            $this->logger->info("Package created for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    private function processCourierAssigned($details)
    {
        $packageId = $details['package']['id'];
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];
        $trackingUrl = $details['package']['tracking_url'];
        $courier = $details['courier']['name'];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'Stuart package (ID: '.$packageId.') is assigned to '.$courier.'. Track your shipment <a href="'.$trackingUrl.'" target="_blank">here</a>.';

            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);

            $this->orderRepository->save($order);
            $this->logger->info("Package assigned to courier for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    private function processCourierArriving($details)
    {
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];
        $task = $details['task'];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'courier is arriving at '.$task.'.';

            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);

            $this->orderRepository->save($order);
            $this->logger->info("Package courier arriving for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    private function processCourierWaiting($details)
    {
        $task = $details['task'];
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];
        $trackingUrl = $details['package']['tracking_url'];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'courier is waiting at '.$task.'. Track your shipment <a href="'.$trackingUrl.'" target="_blank">here</a>.';

            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);

            $this->orderRepository->save($order);
            $this->logger->info("Package courier waiting for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    private function processPackageDelivering($details)
    {
        $packageId = $details['package']['id'];
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];
        $trackingUrl = $details['package']['tracking_url'];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'Stuart package (ID: '.$packageId.') is being delivered. Track your shipment <a href="'.$trackingUrl.'" target="_blank">here</a>.';

            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);
            
            $this->orderRepository->save($order);
            $this->logger->info("Package delivering for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    private function processPackageDelivered($details)
    {
        $packageId = $details['package']['id'];
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'Stuart package (ID: '.$packageId.') is delivered.';
            
            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);

            $this->orderRepository->save($order);
            $this->logger->info("Package delivered for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    private function processPackageCanceled($details)
    {
        $packageId = $details['package']['id'];
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];
        $actor = $details['cancelation']['actor'];
        $reason = $details['cancelation']['reason'];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'Stuart package (ID: '.$packageId.') delivery is canceled by '.$actor.'because of '.$reason;

            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);

            $this->orderRepository->save($order);
            $this->logger->info("Package canceled for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    private function processCourierReassigning($details)
    {
        $packageId = $details['package']['id'];
        $reference = explode('_',$details['package']['reference']);
        $orderId = $reference[0];
        $actor = $details['reassigning']['actor'];
        $reason = $details['reassigning']['reason'];

        // Assuming 'reference' is your order increment ID
        $order = $this->orderRepository->get($orderId);
        
        if ($order) {
            $comment = 'Stuart package (ID: '.$packageId.') delivery is Reassign by '.$actor.'because of '.$reason;

            $order->addCommentToStatusHistory($comment)
                ->setIsVisibleOnFront(true);

            $this->orderRepository->save($order);
            $this->logger->info("Package created for order {$orderId}");
        } else {
            $this->logger->error("Order not found for reference: {$orderId}");
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}