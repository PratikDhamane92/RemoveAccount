<?php
namespace Codi\StuartShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Codi\StuartShipping\Model\StuartJobRepository;
use Codi\StuartShipping\Model\StuartJobFactory;

class SaveStuartJobToOrder implements ObserverInterface
{
    protected $stuartJobRepository;
    protected $stuartJobFactory;

    public function __construct(
        \Codi\StuartShipping\Model\Carrier\Stuart $shippingCarrier,
        StuartJobRepository $stuartJobRepository,
        StuartJobFactory $stuartJobFactory
    ) {
        $this->shippingCarrier = $shippingCarrier;
        $this->stuartJobRepository = $stuartJobRepository;
        $this->stuartJobFactory = $stuartJobFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getShippingMethod() === 'stuart_stuart') {

            $shippingLabel = $this->shippingCarrier->createJobForOrder($order);

            if (array_key_exists("id",$shippingLabel)){
                $order->setStuJobId($shippingLabel['id']);
                $order->save();

                $stuartJob = $this->stuartJobFactory->create();
                $stuartJob->setOrderId($order->getId());
                $stuartJob->setJobId($order->getStuJobId()); // Assuming you've saved this during rate calculation
                $stuartJob->setStatus('created');
                $stuartJob->setTrackingUrl($shippingLabel['deliveries'][0]['tracking_url']);
                $stuartJob->setPickupAt($shippingLabel['pickup_at']);
                $stuartJob->setCreatedAt($shippingLabel['created_at']);

                $this->stuartJobRepository->save($stuartJob);
            }
        }
    }
}