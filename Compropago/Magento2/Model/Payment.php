<?php
/*
 * Copyright 2016 Compropago.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model;


/**
 * Class Payment
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'compropago';
    
    protected $_code = self::CODE;
    
    protected $_isOffline = true;
    protected $_isGateway  = true;
    protected $_canCapture  = true;
    
    protected $_supportedCurrencyCodes = array('USD','MXN');



    /*public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }*/



    /**
     * Initializes injected data
     *
     * @param array $data
     * @return void
     */
    /*protected function initializeData($data = [])
    {
        if (!empty($data['formBlockType'])) {
            $this->_formBlockType = $data['formBlockType'];
        }
    }*/

    
    /**
     * @return string
     */
    public function getPublicKey()
    {
    	return $this->getConfigData('public_key');
    }
    
    /**
     * @return string
     */
    public function getPrivateKey()
    {
    	return $this->getConfigData('private_key');
    }
    
    /**
     * @return boolean
     */
    public function getLiveMode()
    {
    	return 	($this->getConfigData('live_mode')=='1')? true : false;
    }
   
    public function getShowLogos()
    {
    	return $this->getConfigData('showlogos');
    }
    
    /**
     * Availability for currency
     *
     * NOS CONSTA QUE ENTRA DESDE PASO 1
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
    	if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
    		return false;
    	}
    	return true;
    }
}
