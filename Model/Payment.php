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

use \Compropago\Sdk\Client;
use \Compropago\Sdk\Service;

/**
 * Class Payment
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_COMPROPAGO_CODE = 'compropago';   
    const VERSION="2.0.0"; 
    
	
    protected $_compropagoConfig= null;
    protected $_compropagoClient= null;
    protected $_compropagoService= null;
    protected $_modopruebas= null;
    protected $_publickey= null;
    protected $_privatekey= null;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_COMPROPAGO_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = 'Compropago\Magento2\Block\Form\Compropagoform';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Compropago\Magento2\Block\Info\Compropagoinfo';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
    protected $_isGateway  = true;
    protected $_canCapture  = true;
    
    protected $_supportedCurrencyCodes = array('USD','MXN');
    
    protected $_minAmount = null;
    protected $_maxAmount = null;





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
    
    /**
     * @return json
     */
    public function getProviders()
    {
    	if(!$this->_compropagoClient){
    		$this->initConfig();
    	}
    		
    	return $this->_compropagoService->getProviders( );	
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

    
    
    /**
     * Assign data to info model instance
     *
     * ENTRA EN PLACE ORDER
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {

        parent::assignData($data);

        /**
         * Instancia al factory
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();



    	/*if (!$data instanceof \Magento\Framework\DataObject) {
    		$data = new \Magento\Framework\DataObject($data);
    	}
    
    	$this->getInfoInstance()->setPoNumber($data->getPoNumber());
    	return $this;

        /*$array = array(
            array("name" => "Oxxo", "internal_name" => "OXXO"),
            array("name" => "7Eleven", "internal_name" => "7ELEVEN")
        );

        return $array;*/

        return $this;
    }
    
    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
 /*   public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
    	if(empty($this->_publickey) || empty($this->_publickey)){
    		return false;
    	}
    	
    	if ($quote && ( $quote->getBaseGrandTotal() < $this->_minAmount ) ) {
    		return false;
    	}
    	if (!$this->getConfigData('api_key')) {
   			return false;
    	}
   		return parent::isAvailable($quote);
    }*/
}
