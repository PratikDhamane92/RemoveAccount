<?php
namespace Codi\StuartShipping\Api\Data;

interface StuartJobInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const JOB_ID = 'job_id';
    const STATUS = 'status';
    const TRACKING_URL = 'tracking_url';
    const PICKUP_AT = 'pickup_at';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setId($id);

    /**
     * Get Order ID
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set Order ID
     *
     * @param int $orderId
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setOrderId($orderId);

    /**
     * Get Job ID
     *
     * @return string
     */
    public function getJobId();

    /**
     * Set Job ID
     *
     * @param string $jobId
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setJobId($jobId);

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param string $status
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setStatus($status);

    /**
     * Get Tracking URL
     *
     * @return string|null
     */
    public function getTrackingUrl();

    /**
     * Set Tracking URL
     *
     * @param string $trackingUrl
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setTrackingUrl($trackingUrl);

    /**
     * Get Pickup At
     *
     * @return string|null
     */
    public function getPickupAt();

    /**
     * Set Pickup At
     *
     * @param string $pickupAt
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setPickupAt($pickupAt);

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     */
    public function setUpdatedAt($updatedAt);
}