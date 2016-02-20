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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

use Compropago\Sdk\Controllers\Views;
use Compropago\Sdk\Client;
use Compropago\Sdk\Service;



class CompropagoConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Payment::PAYMENT_METHOD_COMPROPAGO_CODE;

    /**
     * @var compropago
     */
    protected $method;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'compropago' => [
                    'data' => 'scope placeholder',            
                	'compropagoProvidersDisplay' => $this->showProviders()
                	
                ],
            ],
        ] : [];
    }

  	/**
  	 * Return providers buffer as string
  	 * @return string
  	 */
    protected function showProviders()
    {
    	//si se puede validar objetos o cambio a API generar en Model primario con try//catch	
    	
    	$compropagoConfig= array(
                'publickey'=>$this->method->getPublicKey(),
                'privatekey'=>$this->method->getPrivateKey(),
                'live'=>$this->method->getLiveMode() 
    			//definir contained 
        );
		
		// Instancia del Client, revisar el motivo por el que  no dejo pasar objetos
    	//$this->method->getCompropagoConfig()
    	
    	$compropagoClient  = new Client($compropagoConfig);
    	$compropagoService = new Service($compropagoClient);
    	
    	$compropagoProviders = $compropagoService->getProviders();    	
    	$compropagoData['providers']= $compropagoProviders;
    	
    	// Generar variables desde administrador /etc/adminhtml/system.xml
    	$compropagoData['showlogo']='yes';                           
    	$compropagoData['description']='Realiza tu pago en OXXO, 7eleven y otras más';
    	$compropagoData['instrucciones']='Seleccione una tienda';  
    	
    	$response = Views::loadView('providers',$compropagoData,'ob');
    	
    	return str_replace('name="compropagoProvider"', 'data-bind="value: compropagoProvider" name="compropagoProvider"', $response);
    }

  
}
