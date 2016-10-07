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

use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;
use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action {

    /**
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
    protected $resultFactory;

    protected $_model;
    /**
     * @var PageFactory
     */
    private $factory;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderData;


    private $message;


    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $factory,
        \Compropago\Magento2\Model\Payment $model,
        \Magento\Sales\Model\Order $orderData
    ) {
        parent::__construct($context);
        $this->_model = $model;
        $this->factory = $factory;
        $this->orderData = $orderData;
    }


    private function webhook($request = null)
    {
        /**
         * Se valida el request y se transforma con la cadena a un objeto de tipo CpOrderInfo con el Factory
         */
        if(empty($request) || !$resp_webhook = Factory::cpOrderInfo($request)){
            die('Tipo de Request no Valido');
        }


        /**
         * Gurdamos la informacion necesaria para el Cliente
         * las llaves de compropago y el modo de ejecucion de la tienda
         */
        $publickey     = $this->_model->getPublicKey();
        $privatekey    = $this->_model->getPrivateKey();
        $live          = $this->_model->getLiveMode();


        /**
         * Se valida que las llaves no esten vacias (No es obligatorio pero si recomendado)
         */
        if (empty($publickey) || empty($privatekey)){
            die("Se requieren las llaves de compropago");
        }





        try{
            /**
             * Se incializa el cliente
             */
            $client = new Client(
                $publickey,
                $privatekey,
                $live
            );

            /**
             * Validamos que nuestro cliente pueda procesar informacion
             */
            Validations::validateGateway($client);
        }catch (\Exception $e) {
            //something went wrong at sdk lvl
            die($e->getMessage());
        }


        /**
         * Verificamos si recivimos una peticion de prueba
         */
        if($resp_webhook->getId()=="ch_00000-000-0000-000000"){
            die("Probando el WebHook?, <b>Ruta correcta.</b>");
        }



        try{
            /**
             * Verificamos la informacion del Webhook recivido
             */
            $response = $client->api->verifyOrder($resp_webhook->getId());


            /**
             * Comprovamos que la verificacion fue exitosa
             */
            if($response->getType() == 'error'){
                die('Error procesando el número de orden');
            }

            $this->orderData->loadByIncrementId($response->getId());

            /**
             * Generamos las rutinas correspondientes para cada uno de los casos posible del webhook
             */
            switch ($response->getType()){
                case 'charge.success':
                    $this->orderData->setState('processing');
                    break;
                case 'charge.pending':
                    $this->orderData->setState('pending_payment');
                    break;
                case 'charge.declined':
                    $this->orderData->setState('canceled');
                    break;
                case 'charge.expired':
                    $this->orderData->setState('canceled');
                    break;
                case 'charge.deleted':
                    $this->orderData->setState('canceled');
                    break;
                case 'charge.canceled':
                    $this->orderData->setState('canceled');
                    break;
                default:
                    die('Invalid Response type');
            }

        }catch (\Exception $e){
            //something went wrong at sdk lvl
            $this->message = $e->getMessage();
            return false;
        }

        return true;
    }


	/**
	 * @return \Magento\Framework\Controller\Result\Raw
	 */
    public function execute()
    {
        /**
         * Se captura la informacion enviada desde compropago
         */
        $request = @file_get_contents('php://input');

    	$res = $this->webhook($request);

        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create('raw');
        $result->setContents($res ? "Done" : $this->message);

        return $result;
    }

}