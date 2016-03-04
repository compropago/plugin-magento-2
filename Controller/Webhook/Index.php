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
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 * @author Rolando Lucio <rolando@compropago.com>
 */


namespace Compropago\Magento2\Controller\Webhook;


use Compropago\Sdk\Client;
use Compropago\Sdk\Service;
use Compropago\Sdk\Utils\Store;

class Index extends \Magento\Framework\App\Action\Action {

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $resultFactory;

    /**
     * Index Controller constructor
     * @param \Magento\Framework\App\Action\Context       $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
    }


    /**
     * Set webhook output
     * @override
     * @return \Magento\Framewrok\Controller\Result\Raw
     */
    public function execute()
    {
    	$message = "";
    	$factory = \Magento\Framework\App\ObjectManager::getInstance();
    	$compropagoModel = $factory->create('\Compropago\Magento2\Model\Payment');

    	$request = @file_get_contents('php://input');

    	try{
			if(!$jsonObj = json_decode($request)){
				throw new \Exception('Tipo de Request no Valido');
			}

			if ( empty( $compropagoModel->getPublicKey() ) || empty( $compropagoModel->getPrivateKey() ) ){
				throw new \Exception("Se requieren las llaves de compropago");
			}
		
			$compropagoConfig= array(
				'publickey'		=> $compropagoModel->getPublicKey(),
				'privatekey'	=> $compropagoModel->getPrivateKey(),
				'live'			=> $compropagoModel->getLiveMode(),
				'contained'		=> 'plugin; cpmg2 1.9.7-dev ; magento2 '.'2.0.2'.'; webhook;'
			);
		
			$compropagoClient = new Client($compropagoConfig);
			$compropagoService = new Service($compropagoClient);


			// Valid Keys?
			if(!$compropagoResponse = $compropagoService->evalAuth()){
				throw new \Exception("ComproPago Error: Llaves no validas");
			}

			// Store Mode Vs ComproPago Mode, Keys vs Mode & combinations
			if(! Store::validateGateway($compropagoClient)){
				throw new \Exception("ComproPago Error: La tienda no se encuentra en un modo de ejecución valido");
			}

			//api normalization
			if($jsonObj->api_version=='1.0'){
				$jsonObj->id=$jsonObj->data->object->id;
				$jsonObj->short_id=$jsonObj->data->object->short_id;
			}


			//webhook Test?
			if($jsonObj->id=="ch_00000-000-0000-000000" || $jsonObj->short_id =="000000"){
				throw new \Exception("Probando el WebHook?, <b>Ruta correcta.</b>");
			}

			
			$response = $compropagoService->verifyOrder($jsonObj->id);
			
			
			switch ($response->type){
				case 'charge.success':
					$nomestatus = "COMPROPAGO_SUCCESS";
					break;
				case 'charge.pending':
					$nomestatus = "COMPROPAGO_PENDING";
					break;
				case 'charge.declined':
					$nomestatus = "COMPROPAGO_DECLINED";
					break;
				case 'charge.expired':
					$nomestatus = "COMPROPAGO_EXPIRED";
					break;
				case 'charge.deleted':
					$nomestatus = "COMPROPAGO_DELETED";
					break;
				case 'charge.canceled':
					$nomestatus = "COMPROPAGO_CANCELED";
					break;
				default:
					die('Invalid Response type');
			}
			
			
			/**
			 * DB transactios here
			 */
			$orderId=$response->order_id;
			$orderData = $factory->create('\Magento\Sales\Model\Order');
				
			//$orderData->load($orderId); //int Order #
			$orderData->loadByIncrementId($orderId); //000000 + Order#
			
			switch($nomestatus){
				case 'COMPROPAGO_SUCCESS':
					$orderData->setState('processing');
				break;
				case 'COMPROPAGO_PENDING':
					$orderData->setState('pending_payment');		
				break;
				case 'COMPROPAGO_DECLINED':
					$orderData->setState('canceled');
				break;
				case 'COMPROPAGO_EXPIRED':
					$orderData->setState('canceled');
				break;
				case 'COMPROPAGO_DELETED':
					$orderData->setState('canceled');
				break;
				case 'COMPROPAGO_CANCELED':
					$orderData->setState('canceled');
				break;
				default:
					$orderData->setState('holded');
			}

    	}catch(\Exception $e){
    		$message = $e->getMessage();
    	}


        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create('raw');
        $result->setContents($message);

        return $result;
    }

}