<?php
namespace Codi\StuartShipping\Api;

use Codi\StuartShipping\Api\Data\StuartJobInterface;

interface StuartJobRepositoryInterface
{
    /**
     * Save Stuart Job
     *
     * @param \Codi\StuartShipping\Api\Data\StuartJobInterface $stuartJob
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(StuartJobInterface $stuartJob);

    /**
     * Retrieve Stuart Job
     *
     * @param int $entityId
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($entityId);

    /**
     * Retrieve Stuart Job by Order ID
     *
     * @param int $orderId
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByOrderId($orderId);

    /**
     * Retrieve Stuart Job by Job ID
     *
     * @param string $jobId
     * @return \Codi\StuartShipping\Api\Data\StuartJobInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByJobId($jobId);

    /**
     * Delete Stuart Job
     *
     * @param \Codi\StuartShipping\Api\Data\StuartJobInterface $stuartJob
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(StuartJobInterface $stuartJob);

    /**
     * Delete Stuart Job by ID
     *
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);
}