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

use Compropago\Sdk\Client;
use Compropago\Sdk\Service;

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
   
	public function initConfig()
    {
	 	
    	$this->_minAmount = $this->getConfigData('min_order_total');
    	$this->_maxAmount = $this->getConfigData('max_order_total');
    	
    	$this->_publickey= $this->getConfigData('public_key');
    	$this->_privatekey= $this->getConfigData('private_key');
    	$this->_modopruebas= $this->getConfigData('live_mode');
    	
    	if(!empty($this->_publickey) && !empty($this->_publickey)){
    		$this->setCompropagoConfig();
    		$this->setCompropagoClientService();
    	}
    }
    
    public function getProviders()
    {	
    	if(!$this->_compropagoClient){
    		$this->initConfig();
    	}
    		
    	return $this->_compropagoService->getProviders( );	
    }
   
    
    /**
     * Get ComproPago Config
     * @return array 
     * @since 2.0.0
     */
    public function getCompropagoConfig()
    {
    	if(!$this->_compropagoClient){
    		$this->initConfig();
    	}
    	return $this->_compropagoConfig;
    }
    
    /**
     * Set ComproPago Config
     * @since 2.0.0
     */
    public function setCompropagoConfig()
    {
    	$this->_compropagoConfig = array(
    			'publickey'=>$this->_publickey,
    			'privatekey'=>$this->_privatekey,
    			'live'=>($this->_modopruebas=='1')? true:false,
    			//\Magento\Framework\App\ProductMetadataInterface::getVersion()
    			'contained'=>'plugin; cpmg2 '.self::VERSION.'; magento '. '2.0.0' .';'
    	);
    }
    
    /**
     * Get Providers Config
     * @return array
     * @since 2.0.0
     */
    
    public function setCompropagoClientService()
    {
    	if(!$this->_compropagoClient){
    		$this->initConfig();
    	}
    	if($this->_compropagoConfig){
	    	$this->_compropagoClient  = new Client($this->_compropagoConfig);
	    	$this->_compropagoService = new Service($this->_compropagoClient);
    	}else{
    		return;
    	}   	
    	/*  
    	 try{
    		...
    	}catch (\Exception $e){
    		//$this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
    		//$this->_logger->error(__('ComproPago Instances Error at Model'));
    		throw new \Magento\Framework\Validator\Exception(__('ComproPago Instances Error at Model'));
    	}
    	*/
    	
    }
    
    public function getCompropagoClient()
    {
    	if(!$this->_compropagoClient){
    		$this->initConfig();
    	}
    	return $this->_compropagoClient;
    }
    
    public function getCompropagoService()
    {
    	if(!$this->_compropagoClient){
    		$this->initConfig();
    	}
    	return $this->_compropagoService;
    }
    
    
    /**
     * Availability for currency
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
