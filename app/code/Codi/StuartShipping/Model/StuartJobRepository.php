<?php
namespace Codi\StuartShipping\Model;

use Codi\StuartShipping\Api\StuartJobRepositoryInterface;
use Codi\StuartShipping\Model\StuartJobFactory;
use Codi\StuartShipping\Model\ResourceModel\StuartJob as StuartJobResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class StuartJobRepository implements StuartJobRepositoryInterface
{
    protected $stuartJobFactory;
    protected $stuartJobResource;

    public function __construct(
        StuartJobFactory $stuartJobFactory,
        StuartJobResource $stuartJobResource
    ) {
        $this->stuartJobFactory = $stuartJobFactory;
        $this->stuartJobResource = $stuartJobResource;
    }

    public function save(\Codi\StuartShipping\Api\Data\StuartJobInterface $stuartJob)
    {
        try {
            $this->stuartJobResource->save($stuartJob);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $stuartJob;
    }

    public function getById($entityId)
    {
        $stuartJob = $this->stuartJobFactory->create();
        $this->stuartJobResource->load($stuartJob, $entityId);
        if (!$stuartJob->getId()) {
            throw new NoSuchEntityException(__('Stuart Job with id "%1" does not exist.', $entityId));
        }
        return $stuartJob;
    }

    public function getByOrderId($orderId)
    {
        $stuartJob = $this->stuartJobFactory->create();
        $this->stuartJobResource->load($stuartJob, $orderId, 'order_id');
        if (!$stuartJob->getId()) {
            throw new NoSuchEntityException(__('Stuart Job for order "%1" does not exist.', $orderId));
        }
        return $stuartJob;
    }

    public function getByJobId($jobId)
    {
        $stuartJob = $this->stuartJobFactory->create();
        $this->stuartJobResource->load($stuartJob, $jobId, 'job_id');
        if (!$stuartJob->getId()) {
            throw new NoSuchEntityException(__('Stuart Job with job id "%1" does not exist.', $jobId));
        }
        return $stuartJob;
    }

    public function delete(\Codi\StuartShipping\Api\Data\StuartJobInterface $stuartJob)
    {
        try {
            $this->stuartJobResource->delete($stuartJob);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        }
        return true;
    }

    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }
}