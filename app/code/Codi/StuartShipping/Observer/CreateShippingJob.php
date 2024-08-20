<?php
namespace Codi\StuartShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Codi\StuartShipping\Model\Carrier\Stuart;
use Codi\StuartShipping\Model\StuartJobRepository;
use Codi\StuartShipping\Model\StuartJobFactory;
use Psr\Log\LoggerInterface;

class CreateShippingJob implements ObserverInterface
{
    protected $stuartCarrier;
    protected $logger;

    public function __construct(
        Stuart $stuartCarrier,
        StuartJobRepository $stuartJobRepository,
        StuartJobFactory $stuartJobFactory,
        ShipmentCommentSender $shipmentNotifier,
        LoggerInterface $logger
    ) {
        $this->stuartCarrier = $stuartCarrier;
        $this->stuartJobRepository = $stuartJobRepository;
        $this->stuartJobFactory = $stuartJobFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        // Check if the shipping method is Stuart
        if ($order->getShippingMethod() === 'stuart_stuart') {
            try {
                $result = $this->stuartCarrier->createJobForOrder($order, $shipment);
                if ($result && isset($result['id'])) {
                    
                    $trackNumber = $result['id'];
                    $trackURL = $result['deliveries'][0]['tracking_url'];

                    // save shipping job id
                    $order->setStuJobId($result['id']);
                    $order->save();

                    // Add shipping comment
                    $shipmentComment = 'Stuart shipping job created. Track Your order <a href="'.$trackURL.'" target="_blank">here</a>.';
                    $shipment->addComment($shipmentComment);
                    $shipment->save();

                    // Notify customer
                    $isCustomerNotified = true;
                    $shipmentComment = html_entity_decode($shipmentComment);
                    $this->shipmentNotifier->send($shipment, $isCustomerNotified, $shipmentComment);
                    $shipment->save();

                    // Save shipping data in table
                    $stuartJob = $this->stuartJobFactory->create();
                    $stuartJob->setOrderId($order->getId());
                    $stuartJob->setJobId($order->getStuJobId()); // Assuming you've saved this during rate calculation
                    $stuartJob->setStatus('created');
                    $stuartJob->setTrackingUrl($result['deliveries'][0]['tracking_url']);
                    $stuartJob->setPickupAt($result['pickup_at']);
                    $stuartJob->setCreatedAt($result['created_at']);

                    $this->stuartJobRepository->save($stuartJob);

                } else {
                    $this->logger->error("Failed to create Stuart shipping job for order " . $order->getIncrementId());
                }
            } catch (\Exception $e) {
                $this->logger->error("Error creating Stuart shipping job: " . $e->getMessage());
            }
        }
    }
}