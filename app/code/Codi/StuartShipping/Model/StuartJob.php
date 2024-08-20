<?php
namespace Codi\StuartShipping\Model;

use Magento\Framework\Model\AbstractModel;
use Codi\StuartShipping\Api\Data\StuartJobInterface;

class StuartJob extends AbstractModel implements StuartJobInterface
{
    protected function _construct()
    {
        $this->_init(\Codi\StuartShipping\Model\ResourceModel\StuartJob::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getJobId()
    {
        return $this->getData(self::JOB_ID);
    }

    /**
     * @inheritDoc
     */
    public function setJobId($jobId)
    {
        return $this->setData(self::JOB_ID, $jobId);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getTrackingUrl()
    {
        return $this->getData(self::TRACKING_URL);
    }

    /**
     * @inheritDoc
     */
    public function setTrackingUrl($trackingUrl)
    {
        return $this->setData(self::TRACKING_URL, $trackingUrl);
    }

    /**
     * @inheritDoc
     */
    public function getPickupAt()
    {
        return $this->getData(self::PICKUP_AT);
    }

    /**
     * @inheritDoc
     */
    public function setPickupAt($pickupAt)
    {
        return $this->setData(self::PICKUP_AT, $pickupAt);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}