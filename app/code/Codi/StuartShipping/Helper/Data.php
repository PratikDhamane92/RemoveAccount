<?php
namespace Codi\StuartShipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $encryptor;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    public function getMode()
    {
        return $this->scopeConfig->getValue('carriers/stuart/mode', ScopeInterface::SCOPE_STORE);
    }

    public function getShippingTitle()
    {
        return $this->scopeConfig->getValue('carriers/stuart/title', ScopeInterface::SCOPE_STORE);
    }

    public function getApiUrl()
    {
        $mode = $this->getMode();
        $configPath = 'carriers/stuart/' . $mode . '_api_url';
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    public function getClientId()
    {
        $mode = $this->getMode();
        $configPath = 'carriers/stuart/' . $mode . '_client_id';
        $encryptedValue = $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($encryptedValue);
    }

    public function getClientSecret()
    {
        $mode = $this->getMode();
        $configPath = 'carriers/stuart/' . $mode . '_client_secret';
        $encryptedValue = $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($encryptedValue);
    }

}