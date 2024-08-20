<?php
namespace Codi\StuartShipping\Model\Api;

use Magento\Framework\HTTP\Client\Curl;
use Codi\StuartShipping\Helper\Data as StuartHelper;

class StuartApiClient
{
    protected $curl;
    protected $helper;
    protected $token;

    public function __construct(
        Curl $curl,
        StuartHelper $helper
    ) {
        $this->curl = $curl;
        $this->helper = $helper;
    }

    protected function authenticate()
    {
        $apiUrl = $this->helper->getApiUrl();
        $clientId = $this->helper->getClientId();
        $clientSecret = $this->helper->getClientSecret();

        $this->curl->setOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $this->curl->post(
            $apiUrl . '/oauth/token',
            json_encode([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'api',
                'grant_type' => 'client_credentials'
            ])
        );

        $response = json_decode($this->curl->getBody(), true);
        $this->token = $response['access_token'];
    }

    public function getJobPricing($jobData)
    {
        if (!$this->token) {
            $this->authenticate();
        }

        $apiUrl = $this->helper->getApiUrl();
        $this->curl->setOption(CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);
        $this->curl->post($apiUrl . '/v2/jobs/pricing', json_encode($jobData));

        return json_decode($this->curl->getBody(), true);
    }

    public function createJob($jobData)
    {
        if (!$this->token) {
            $this->authenticate();
        }

        $apiUrl = $this->helper->getApiUrl();
        $this->curl->setOption(CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);
        $this->curl->post($apiUrl . '/v2/jobs', json_encode($jobData));

        return json_decode($this->curl->getBody(), true);
    }

    public function registerWebhook($url)
    {
        if (!$this->token) {
            $this->authenticate();
        }

        $apiUrl = $this->helper->getApiUrl();
        $this->curl->setOption(CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);
        $this->curl->post($apiUrl . '/v2/webhooks', json_encode([
            'url' => $url,
            'version' => 'v3',
            'topics' => [
                'package_created',
                'courier_assigned',
                'courier_arriving',
                'courier_waiting',
                'package_delivering',
                'package_delivered',
                'package_canceled',
                'courier_reassigning'
            ],
            'enabled' => true
        ]));

        return json_decode($this->curl->getBody(), true);
    }

    public function getJobStatus($jobId)
    {
        if (!$this->token) {
            $this->authenticate();
        }

        $apiUrl = $this->helper->getApiUrl();
        $this->curl->setOption(CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token
        ]);
        $this->curl->get($apiUrl . '/v2/jobs/' . $jobId);

        return json_decode($this->curl->getBody(), true);
    }
}