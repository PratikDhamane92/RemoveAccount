<?php

/**
 * Codi_CustomerAccount
 */
namespace Codi\RemoveAccount\Block\Customer;

use Magento\Framework\View\Element\Template;

class Delete extends Template
{
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }
}