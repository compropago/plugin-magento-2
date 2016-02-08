<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Compropago\Payments\Block\Info;

class Payment extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_payableTo;

    /**
     * @var string
     */
    protected $_mailingAddress;

    /**
     * @var string
     */
    protected $_template = 'Compropago_Payment::info/providers.phtml';

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getPayableTo()
    {
        if ($this->_payableTo === null) {
            $this->_convertAdditionalData();
        }
        return $this->_payableTo;
    }

  public function getProviders()
    {
    	return "Compropago Providers goes here block info";
    }
    public function getMailingAddress()
    {
        if ($this->_mailingAddress === null) {
            $this->_convertAdditionalData();
        }
        return $this->_mailingAddress;
    }

    /**
     * Enter description here...
     *
     * @return $this
     */
    protected function _convertAdditionalData()
    {
        $details = @unserialize($this->getInfo()->getAdditionalData());
        if (is_array($details)) {
            $this->_payableTo = isset($details['public_key']) ? (string)$details['public_key'] : '';
            $this->_mailingAddress = isset($details['public_key']) ? (string)$details['public_key'] : '';
        } else {
            $this->_payableTo = '';
            $this->_mailingAddress = '';
        }
        return $this;
    }


}
