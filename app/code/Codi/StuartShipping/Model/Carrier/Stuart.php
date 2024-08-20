<?php
namespace Codi\StuartShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Codi\StuartShipping\Model\Api\StuartApiClient;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;

class Stuart extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'stuart';
    protected $apiClient;
    protected $_rateFactory;
    protected $_rateMethodFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\loggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        StuartApiClient $apiClient,
        StoreManagerInterface $storeManager,
        TrackFactory $trackFactory,
        array $data = []
    ) {
        $this->_rateFactory = $rateFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->currencyFactory = $currencyFactory;
        $this->apiClient = $apiClient;
        $this->storeManager = $storeManager;
        $this->trackFactory = $trackFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // Check if shipping is restricted to specific countries
        if ($this->getConfigData('sallowspecific') == 1) {
            $allowedCountries = explode(',', $this->getConfigData('specificcountry'));
            $shippingCountry = $request->getDestCountryId();
            
            if (!in_array($shippingCountry, $allowedCountries)) {
                return false;
            }
        }

        $this->_logger->info("Inside collect rates");

        $result = $this->_rateFactory->create();
        $baseCurrency = $this->storeManager->getStore()->getDefaultCurrencyCode();

        $jobData = $this->prepareJobData($request);
        $this->_logger->info(print_r($jobData,true));

        $apiResponse = $this->apiClient->getJobPricing($jobData);
        $this->_logger->info(print_r($apiResponse,true));

        if (isset($apiResponse['amount_with_tax'])) {

            $shippingPrice = $apiResponse['amount_with_tax'];
            $shipCurr = $apiResponse['currency'];

            $shipAmt = $this->convertPrice($shippingPrice,$shipCurr,$baseCurrency);
            $this->_logger->info("job pricing".$shipAmt);

            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod('stuart');
            $rate->setMethodTitle('Stuart Delivery');
            $rate->setPrice($shipAmt);
            $rate->setCost($shipAmt);

            $result->append($rate);
        }

        return $result;
    }

    private function prepareJobData(RateRequest $request)
    {
        // Prepare job data based on the rate request
        $quote = $request->getAllItems()[0]->getQuote();
        $items = $quote->getAllVisibleItems();

        $package_type = "";
        // Initialize total weight in kg
        $totalWeightInKg = 0;

        // Conversion factor from pounds to kilograms
        $lbsToKgFactor = 0.453592;

        $weightUnit = $this->_scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        foreach ($items as $item) {
            $product = $item->getProduct();
            $weight = $product->getWeight();
            $qty = $item->getQty();

            // Convert weight to kg if necessary
            if ($weightUnit == 'lbs') {
                $weight = $weight * $lbsToKgFactor;
            }

            // Add the weight to the total, taking into account the quantity
            $totalWeightInKg += $weight * $qty;
        }

        if($totalWeightInKg <= 3){
            $package_type = "xsmall";
        }elseif($totalWeightInKg > 3 && $totalWeightInKg <= 6){
            $package_type = "small";
        }elseif($totalWeightInKg > 6 && $totalWeightInKg <= 12){
            $package_type = "medium";
        }elseif($totalWeightInKg > 12 && $totalWeightInKg <= 40){
            $package_type = "large";
        }else{
            $package_type = "xlarge";
        }

        // Get origin address from system configuration
        $originStreet = $this->_scopeConfig->getValue('shipping/origin/street_line1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $originCity = $this->_scopeConfig->getValue('shipping/origin/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $originPostcode = $this->_scopeConfig->getValue('shipping/origin/postcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $originCountryId = $this->_scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        // This is a simplified version, you'll need to adapt it to your needs
        return [
            'job' => [
                'pickup_at' => date('Y-m-d\TH:i:s\Z', strtotime('+1 hour')),
                'pickups' => [
                    [
                        'address' => $originStreet . ', ' . $originCity . ', ' . $originPostcode . ', ' . $originCountryId
                    ]
                ],
                'dropoffs' => [
                    [
                        'address' => $request->getDestStreet() . ', ' . $request->getDestCity() . ', ' . $request->getDestPostcode() . ', ' . $request->getDestCountryId(),
                        'package_type' => $package_type,
                        'package_description' => 'Package'
                    ]
                ]
            ]
        ];
    }

    public function convertPrice($amount,$currency,$baseCurrency)
    {
        $rate = $this->currencyFactory->create()->load($currency)->getAnyRate($baseCurrency);
        $returnValue = $amount * $rate;
        $this->_logger->info("shipping rate".$rate);
        $this->_logger->info("shipping value".$returnValue);

        return $returnValue;
    }

    public function getAllowedMethods()
    {
        return ['stuart' => $this->getConfigData('name')];
    }

    public function createJobForOrder($order, $shipment)
    {
        $jobData = $this->prepareJobDataForOrder($order, $shipment);
        $this->_logger->info(print_r($jobData, true));

        $apiResponse = $this->apiClient->createJob($jobData);
        $this->_logger->info(print_r($apiResponse, true));

        return $apiResponse;
    }

    private function prepareJobDataForOrder($order, $shipment)
    {
        // Prepare job data based on the order

        $shippingAddress = $order->getShippingAddress();
        $originAddress = $this->getOriginAddress();

        $package_type = "";
        // Initialize total weight in kg
        $totalWeightInKg = 0;

        // Conversion factor from pounds to kilograms
        $lbsToKgFactor = 0.453592;

        $weightUnit = $this->_scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        foreach ($shipment->getAllItems() as $item) {

            $orderItem = $item->getOrderItem();
            $weight = $orderItem->getWeight();
            $qty = $item->getQty();

            // Convert weight to kg if necessary
            if ($weightUnit == 'lbs') {
                $weight = $weight * $lbsToKgFactor;
            }

            // Add the weight to the total, taking into account the quantity
            $totalWeightInKg += $weight * $qty;
        }

        if($totalWeightInKg <= 3){
            $package_type = "xsmall";
        }elseif($totalWeightInKg > 3 && $totalWeightInKg <= 6){
            $package_type = "small";
        }elseif($totalWeightInKg > 6 && $totalWeightInKg <= 12){
            $package_type = "medium";
        }elseif($totalWeightInKg > 12 && $totalWeightInKg <= 40){
            $package_type = "large";
        }else{
            $package_type = "xlarge";
        }

        $client_reference = $order->getIncrementId().'_'.$shipment->getId();

        // This is a simplified version, you'll need to adapt it to your needs
        return [
            'job' => [
                'pickup_at' => date('Y-m-d\TH:i:s\Z', strtotime('+10 minutes')),
                'pickups' => [
                    [
                        'address' => $originAddress
                    ]
                ],
                'dropoffs' => [
                    [
                        'package_type' => $package_type,
                        'client_reference' => $client_reference,
                        'address' => $this->formatAddress($shippingAddress),
                        'comment' => $shippingAddress->getCustomerName(),
                        'contact' => [
                            'firstname' => $shippingAddress->getFirstname(),
                            'lastname' => $shippingAddress->getLastname(),
                            'phone' => $shippingAddress->getTelephone(),
                            'email' => $shippingAddress->getEmail(),
                            'company' => ""
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getOriginAddress()
    {
        $originStreet = $this->_scopeConfig->getValue('shipping/origin/street_line1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $originCity = $this->_scopeConfig->getValue('shipping/origin/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $originPostcode = $this->_scopeConfig->getValue('shipping/origin/postcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $originCountryId = $this->_scopeConfig->getValue('shipping/origin/country_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return "$originStreet, $originCity, $originPostcode, $originCountryId";
    }

    private function formatAddress($address)
    {
        $custAddr = "";
        $shipStreet = $address->getStreet();

        $custAddr = $shipStreet[0];
        if (array_key_exists(1,$shipStreet)){
            $custAddr .= ", ".$shipStreet[1];
        }
        if($address->getCity()){
            $custAddr .= ", ".$address->getCity();
        }
        if($address->getRegion()){
            $custAddr .= ", ".$address->getRegion();
        }
        if($address->getCountryId()){
            $custAddr .= ", ".$address->getCountryId();
        }
        if($address->getPostcode()){
            $custAddr .= ", ".$address->getPostcode();
        }

        return $custAddr;
    }
}